<?php

/**
 * This file contains the ImportTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Import;

use Lunr\Ticks\Profiling\Profiler;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Pipeline\Import\Diff;
use Pipeline\Import\Flag;
use Pipeline\Import\Import;
use Pipeline\Import\ImportInfo;
use Pipeline\Import\ImportLocator;
use Pipeline\Import\ImportTargetInterface;
use Pipeline\Import\ItemObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the Import class.
 */
#[CoversClass(Import::class)]
abstract class ImportTestCase extends MockeryTestCase
{

    /**
     * Mock instance of the import locator.
     * @var ImportLocator&MockInterface
     */
    protected ImportLocator&MockInterface $locator;

    /**
     * Mock instance of the diff algorithm.
     * @var Diff&MockInterface
     */
    protected Diff&MockInterface $diff;

    /**
     * Mock instance of the item observer.
     * @var ItemObserverInterface&MockInterface
     */
    protected ItemObserverInterface&MockInterface $observer;

    /**
     * Mock instance of the import target.
     * @var ImportTargetInterface&MockInterface
     */
    protected ImportTargetInterface&MockInterface $target;

    /**
     * Mock instance of the Logger.
     * @var LoggerInterface&MockInterface
     */
    protected LoggerInterface&MockInterface $logger;

    /**
     * Mock instance of the ImportInfo.
     * @var ImportInfo&MockInterface
     */
    protected ImportInfo&MockInterface $info;

    /**
     * Mock instance of the Profiler.
     * @var Profiler&MockInterface
     */
    protected Profiler&MockInterface $profiler;

    /**
     * Instance of the tested class.
     * @var Import
     */
    protected Import $class;

    /**
     * TestCase Constructor.
     */
    public function setUp(): void
    {
        $this->locator  = Mockery::mock(ImportLocator::class);
        $this->diff     = Mockery::mock(Diff::class);
        $this->observer = Mockery::mock(ItemObserverInterface::class);
        $this->target   = Mockery::mock(ImportTargetInterface::class);
        $this->logger   = Mockery::mock(LoggerInterface::class);
        $this->info     = Mockery::mock(ImportInfo::class);
        $this->profiler = Mockery::mock(Profiler::class);

        $this->locator->shouldReceive('getLogger')
                      ->once()
                      ->andReturn($this->logger);

        $this->class = new Import($this->locator, $this->diff, $this->observer, $this->target);
    }

    /**
     * TestCase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->locator);
        unset($this->diff);
        unset($this->observer);
        unset($this->target);
        unset($this->logger);
        unset($this->info);
        unset($this->profiler);
        unset($this->class);
    }

    /**
     * Set up default expectations for a full updateData() call with a single item.
     *
     * @param array $new New data items
     *
     * @return void
     */
    protected function setupDefaultExpectations(array $new): void
    {
        $this->info->shouldReceive('getTargetIdentifier')
                   ->once()
                   ->andReturn('target_name');

        $this->target->shouldReceive('setTarget')
                     ->with('target_name')
                     ->once();

        $this->info->shouldReceive('getContentRanges')
                   ->once()
                   ->andReturn([]);

        $this->info->shouldReceive('hasFlag')
                   ->with(Flag::IgnoreTimeKeyOnlyChange)
                   ->once()
                   ->andReturn(FALSE);

        $this->info->shouldReceive('getProfiler')
                   ->times(5)
                   ->andReturn($this->profiler);

        $this->logger->shouldReceive('notice')
                     ->with(Mockery::type('string'))
                     ->times(5);

        $this->profiler->shouldReceive('startNewSpan')
                       ->with(Mockery::type('string'))
                       ->times(5);

        $this->target->shouldReceive('getData')
                     ->with(Mockery::type('array'), [])
                     ->once()
                     ->andReturn([]);

        $this->target->shouldReceive('getUniqueKeys')
                     ->once()
                     ->andReturn([]);

        $this->observer->shouldReceive('getItemIdentifier')
                       ->once()
                       ->andReturn('id1');

        $this->diff->shouldReceive('diff')
                   ->once();

        $this->diff->shouldReceive('getNewData')
                   ->once()
                   ->andReturn([]);

        $this->diff->shouldReceive('getUpdatedData')
                   ->once()
                   ->andReturn([]);

        $this->diff->shouldReceive('getObsoleteData')
                   ->once()
                   ->andReturn([]);

        $this->diff->shouldReceive('getSkippedData')
                   ->once()
                   ->andReturn([]);

        $this->diff->shouldReceive('getSameData')
                   ->once()
                   ->andReturn([]);

        $this->info->shouldReceive('setResults')
                   ->with(0, 0, 0, 0, 0)
                   ->once();

        $this->target->shouldReceive('updateData')
                     ->with(Mockery::type('array'), [])
                     ->once()
                     ->andReturn(0);
    }

}

?>
