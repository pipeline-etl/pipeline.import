<?php

/**
 * This file contains the ImportDeleteDataResultsTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Import;

use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Pipeline\Import\Import;

/**
 * This class contains tests for the result counts and mismatch warning in the deleteData() method.
 */
#[CoversClass(Import::class)]
class ImportDeleteDataResultsTest extends ImportTestCase
{

    /**
     * Set up default expectations for a deleteData() result test.
     *
     * @param int $obsoleteCount Count of obsolete diff items
     * @param int $importResult  Result from target updateData
     *
     * @return void
     */
    private function setupResultExpectations(int $obsoleteCount, int $importResult): void
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

        $this->info->shouldReceive('getProfiler')
                   ->times(4)
                   ->andReturn($this->profiler);

        $this->logger->shouldReceive('notice')
                     ->with(Mockery::type('string'))
                     ->times(4);

        $this->profiler->shouldReceive('startNewSpan')
                       ->with(Mockery::type('string'))
                       ->times(4);

        $this->target->shouldReceive('getUniqueKeys')
                     ->once()
                     ->andReturn([]);

        $this->observer->shouldReceive('getItemIdentifier')
                       ->once()
                       ->andReturn('id1');

        $this->diff->shouldReceive('diff')
                   ->once();

        $this->diff->shouldReceive('getObsoleteData')
                   ->once()
                   ->andReturn(array_fill(0, $obsoleteCount, [ 'id' => '1' ]));

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
        $delete = [ [ 'id' => '1' ] ];

        $this->setupResultExpectations(1, 1);

        $this->info->shouldReceive('setResults')
                   ->with(0, 1, 0, 0, 0)
                   ->once();

        $this->class->deleteData($delete, $this->info);
    }

    /**
     * Test that a warning is logged when result is zero but changes were detected.
     */
    public function testWarningLoggedWhenResultZeroButChangesDetected(): void
    {
        $delete = [ [ 'id' => '1' ] ];

        $this->setupResultExpectations(1, 0);

        $this->info->shouldReceive('setResults')
                   ->with(0, 1, 0, 0, 0)
                   ->once();

        $this->logger->shouldReceive('warning')
                     ->with('Import reported changes but DB reported nothing changed!')
                     ->once();

        $this->class->deleteData($delete, $this->info);
    }

    /**
     * Test that no warning is logged when result is zero and no changes were detected.
     */
    public function testNoWarningWhenResultZeroAndNoChanges(): void
    {
        $delete = [];

        $this->info->shouldReceive('getTargetIdentifier')
                   ->once()
                   ->andReturn('target_name');

        $this->target->shouldReceive('setTarget')
                     ->with('target_name')
                     ->once();

        $this->info->shouldReceive('getContentRanges')
                   ->once()
                   ->andReturn([]);

        $this->info->shouldReceive('getProfiler')
                   ->times(4)
                   ->andReturn($this->profiler);

        $this->logger->shouldReceive('notice')
                     ->with(Mockery::type('string'))
                     ->times(4);

        $this->profiler->shouldReceive('startNewSpan')
                       ->with(Mockery::type('string'))
                       ->times(4);

        $this->target->shouldReceive('getUniqueKeys')
                     ->once()
                     ->andReturn([]);

        $this->diff->shouldReceive('diff')
                   ->with([], [])
                   ->once();

        $this->diff->shouldReceive('getObsoleteData')
                   ->once()
                   ->andReturn([]);

        $this->info->shouldReceive('setResults')
                   ->with(0, 0, 0, 0, 0)
                   ->once();

        $this->target->shouldReceive('updateData')
                     ->with(Mockery::type('array'), [])
                     ->once()
                     ->andReturn(0);

        $this->logger->shouldNotReceive('warning');

        $this->class->deleteData($delete, $this->info);
    }

}

?>
