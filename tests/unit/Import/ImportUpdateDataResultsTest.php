<?php

/**
 * This file contains the ImportUpdateDataResultsTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Import;

use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Pipeline\Import\Flag;
use Pipeline\Import\Import;

/**
 * This class contains tests for the result counts and mismatch warning in the Import class.
 */
#[CoversClass(Import::class)]
class ImportUpdateDataResultsTest extends ImportTestCase
{

    /**
     * Set up expectations that return specific diff counts.
     *
     * @param int $newCount      Count of new diff items
     * @param int $obsoleteCount Count of obsolete diff items
     * @param int $changedCount  Count of changed diff items
     * @param int $skippedCount  Count of skipped diff items
     * @param int $sameCount     Count of same diff items
     * @param int $importResult  Result from target updateData
     *
     * @return void
     */
    private function setupResultExpectations(
        int $newCount,
        int $obsoleteCount,
        int $changedCount,
        int $skippedCount,
        int $sameCount,
        int $importResult
    ): void
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
                     ->with([ 'id' ], [])
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
                   ->andReturn(array_fill(0, $newCount, [ 'id' => '1' ]));

        $this->diff->shouldReceive('getUpdatedData')
                   ->once()
                   ->andReturn(array_fill(0, $changedCount, [ 'id' => '1' ]));

        $this->diff->shouldReceive('getObsoleteData')
                   ->once()
                   ->andReturn(array_fill(0, $obsoleteCount, [ 'id' => '1' ]));

        $this->diff->shouldReceive('getSkippedData')
                   ->once()
                   ->andReturn(array_fill(0, $skippedCount, [ 'id' => '1' ]));

        $this->diff->shouldReceive('getSameData')
                   ->once()
                   ->andReturn(array_fill(0, $sameCount, [ 'id' => '1' ]));

        $this->target->shouldReceive('updateData')
                     ->with(Mockery::type('array'), [])
                     ->once()
                     ->andReturn($importResult);
    }

    /**
     * Test that setResults is called with correct counts.
     */
    public function testSetResultsCalledWithCorrectCounts(): void
    {
        $new = [ [ 'id' => '1' ] ];

        $this->setupResultExpectations(2, 1, 3, 1, 5, 6);

        $this->info->shouldReceive('setResults')
                   ->with(2, 1, 3, 1, 5)
                   ->once();

        $this->class->updateData($new, $this->info);
    }

    /**
     * Test that a warning is logged when result is zero but changes were detected.
     */
    public function testWarningLoggedWhenResultZeroButChangesDetected(): void
    {
        $new = [ [ 'id' => '1' ] ];

        $this->setupResultExpectations(1, 0, 0, 0, 0, 0);

        $this->info->shouldReceive('setResults')
                   ->with(1, 0, 0, 0, 0)
                   ->once();

        $this->logger->shouldReceive('warning')
                     ->with('Import reported changes but DB reported nothing changed!')
                     ->once();

        $this->class->updateData($new, $this->info);
    }

    /**
     * Test that no warning is logged when result is zero and no changes were detected.
     */
    public function testNoWarningWhenResultZeroAndNoChanges(): void
    {
        $new = [ [ 'id' => '1' ] ];

        $this->setupResultExpectations(0, 0, 0, 0, 5, 0);

        $this->info->shouldReceive('setResults')
                   ->with(0, 0, 0, 0, 5)
                   ->once();

        $this->logger->shouldNotReceive('warning');

        $this->class->updateData($new, $this->info);
    }

}

?>
