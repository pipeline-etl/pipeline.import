<?php

/**
 * This file contains the Pipeline Import class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Import;

use Pipeline\Common\Node;
use Pipeline\Import\Exceptions\DatabaseException;
use Psr\Log\LoggerInterface;

/**
 * Class for importing data.
 *
 * @phpstan-import-type ProcessedItem from Node
 */
class Import
{

    /**
     * Shared instance of the import target
     * @var ImportTargetInterface
     */
    protected readonly ImportTargetInterface $target;

    /**
     * Shared instance of the diff algorithm
     * @var Diff
     */
    protected readonly Diff $diff;

    /**
     * Shared instance of an item observer
     * @var ItemObserverInterface
     */
    protected readonly ItemObserverInterface $observer;

    /**
     * Shared instance of the Logger
     * @var LoggerInterface
     */
    protected readonly LoggerInterface $logger;

    /**
     * Shared instance of the pipeline locator.
     * @var ImportLocator
     */
    protected readonly ImportLocator $locator;

    /**
     * Constructor.
     *
     * @param ImportLocator         $locator  The pipeline locator
     * @param Diff                  $diff     The diff algorithm to use
     * @param ItemObserverInterface $observer An item observer
     * @param ImportTargetInterface $target   The import target to use
     */
    public function __construct(ImportLocator $locator, Diff $diff, ItemObserverInterface $observer, ImportTargetInterface $target)
    {
        $this->target   = $target;
        $this->locator  = $locator;
        $this->diff     = $diff;
        $this->observer = $observer;
        $this->logger   = $this->locator->getLogger();
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        // no-op
    }

    /**
     * Update information.
     *
     * @param ProcessedItem[] $new    Newly fetched information
     * @param ImportInfo      $info   Pipeline metainfo
     * @param bool            $dryRun Whether to perform a dry-run (don't modify data) or not
     *
     * @return int Number of data items updated
     */
    public function updateData(array &$new, ImportInfo $info, bool $dryRun = FALSE): int
    {
        $this->target->setTarget($info->getTargetIdentifier());

        $keys = isset($new[0]) ? array_keys($new[0]) : NULL;

        $this->reportStep('Configure content range', $info);
        $range = $this->configureContentRange($new, $info);

        $this->reportStep('Get current data', $info);
        $old = $this->getCurrentData($keys, $range);

        $this->reportStep('Removing non unique items', $info);
        $this->removeNonUniqueItems($new);

        $this->reportStep('Create data diff', $info);

        if ($info->hasFlag(Flag::IgnoreTimeKeyOnlyChange))
        {
            $this->diff->skipTimeKeyOnlyChange(value: TRUE);
        }

        $this->diff->diff($old, $new);

        $data = [
            'new'      => $this->diff->getNewData(),
            'updated'  => $this->diff->getUpdatedData(),
            'obsolete' => $this->diff->getObsoleteData(),
        ];

        $newCount      = count($data['new']);
        $obsoleteCount = count($data['obsolete']);
        $changedCount  = count($data['updated']);
        $skippedCount  = count($this->diff->getSkippedData());
        $sameCount     = count($this->diff->getSameData());

        $info->setResults($newCount, $obsoleteCount, $changedCount, $skippedCount, $sameCount);

        if ($dryRun === TRUE)
        {
            return 0;
        }

        try
        {
            $this->reportStep('Update data in database', $info);
            $importResult = $this->target->updateData($data, $range);
        }
        catch (DatabaseException $e)
        {
            $e->setMessage('Failed updating data in the database!');

            throw $e;
        }

        if ($importResult === 0 && ($newCount + $obsoleteCount + $changedCount !== 0))
        {
            $this->logger->warning('Import reported changes but DB reported nothing changed!');
        }

        return $importResult;
    }

    /**
     * Create elements depicting the wanted range of the selection
     *
     * @param ProcessedItem[] $items List of items in the update
     * @param ImportInfo      $info  Pipeline metadata
     *
     * @return ContentRangeInterface[] Array of RangeInterface classes
     */
    protected function configureContentRange(array $items, ImportInfo $info): array
    {
        $ranges = [];

        foreach ($info->getContentRanges() as $range)
        {
            foreach ($range as $name => $config)
            {
                $class = $this->locator->getContentRange($name);

                if (!is_object($class))
                {
                    continue;
                }

                $class->setData($items, $config);

                $ranges[] = $class;
            }
        }

        return $ranges;
    }

    /**
     * Get latest information.
     *
     * @param string[]|null           $keys  The keys to fetch from the data
     * @param ContentRangeInterface[] $range The range to fetch data for
     *
     * @return ProcessedItem[] Array of currently stored information.
     */
    protected function getCurrentData(?array $keys = NULL, array $range = []): array
    {
        try
        {
            $current = $this->target->getData($keys, $range);
        }
        catch (DatabaseException $e)
        {
            $e->setMessage('Error fetching current information from the database!');

            throw $e;
        }

        return $current;
    }

    /**
     * Remove items that do not have a unique identifier
     *
     * @param ProcessedItem[] $data Data to process
     *
     * @return void
     */
    protected function removeNonUniqueItems(array &$data): void
    {
        $uniqueIdentifiers = [];

        $uniqueKeys = $this->target->getUniqueKeys();

        foreach ($data as $key => &$value)
        {
            $identifier = $this->observer->getItemIdentifier($value);

            $uniqueIdentifiers['PRIMARY'][$identifier][] = $key;

            foreach ($uniqueKeys as $index)
            {
                $identifier = $this->observer->getUniqueIdentifier($value, $index['keys']);

                $uniqueIdentifiers[$index['name']][$identifier][] = $key;
            }
        }

        unset($value);

        foreach ($uniqueIdentifiers as $indexName => &$index)
        {
            foreach ($index as $identifier => &$itemKeys)
            {
                $count = count($itemKeys);
                if ($count <= 1)
                {
                    continue;
                }

                /** @var ProcessedItem[] $items */
                $items = [];
                foreach ($itemKeys as $key)
                {
                    if (!array_key_exists($key, $data))
                    {
                        continue;
                    }

                    $items[] = $data[$key];
                }

                $deletableKeys = $itemKeys;
                if (array_intersect_assoc(...$items) === $items[0])
                {
                    //skip over first item
                    array_shift($deletableKeys);
                }

                $this->logger->warning('{action} {amount} items with identical identifier for key \'{index}\': "{identifier}"', [
                    'amount'     => count($items),
                    'identifier' => $identifier,
                    'action'     => $count === count($deletableKeys) ? 'Removing' : 'Squashing',
                    'index'      => $indexName,
                ]);

                foreach ($deletableKeys as $key)
                {
                    unset($data[$key]);
                }
            }

            unset($items);
        }

        $data = array_values($data);
    }

    /**
     * Report a pipeline step
     *
     * @param string     $message Message to log
     * @param ImportInfo $info    Info object to report to
     *
     * @return void
     */
    private function reportStep(string $message, ImportInfo $info): void
    {
        $this->logger->notice($message);
        $info->getProfiler()->startNewSpan($message);
    }

}

?>
