<?php

/**
 * This file contains the ImportDeleteDataDryRunTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Import;

use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Pipeline\Import\Import;

/**
 * This class contains tests for the dry-run behavior of the deleteData() method.
 */
#[CoversClass(Import::class)]
class ImportDeleteDataDryRunTest extends ImportTestCase
{

    /**
     * Set up default dry-run expectations for deleteData().
     *
     * @return void
     */
    private function setupDryRunExpectations(): void
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
                   ->times(3)
                   ->andReturn($this->profiler);

        $this->logger->shouldReceive('notice')
                     ->with(Mockery::type('string'))
                     ->times(3);

        $this->profiler->shouldReceive('startNewSpan')
                       ->with(Mockery::type('string'))
                       ->times(3);

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
                   ->andReturn([ [ 'id' => '1' ] ]);

        $this->info->shouldReceive('setResults')
                   ->with(0, 1, 0, 0, 0)
                   ->once();
    }

    /**
     * Test that dry-run returns zero.
     */
    public function testDryRunReturnsZero(): void
    {
        $delete = [ [ 'id' => '1' ] ];

        $this->setupDryRunExpectations();

        $result = $this->class->deleteData($delete, $this->info, TRUE);

        $this->assertSame(0, $result);
    }

    /**
     * Test that dry-run does not call target updateData.
     */
    public function testDryRunDoesNotCallTargetUpdateData(): void
    {
        $delete = [ [ 'id' => '1' ] ];

        $this->setupDryRunExpectations();

        $this->target->shouldNotReceive('updateData');

        $this->class->deleteData($delete, $this->info, TRUE);
    }

    /**
     * Test that dry-run still sets results.
     */
    public function testDryRunStillSetsResults(): void
    {
        $delete = [ [ 'id' => '1' ] ];

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
                   ->times(3)
                   ->andReturn($this->profiler);

        $this->logger->shouldReceive('notice')
                     ->with(Mockery::type('string'))
                     ->times(3);

        $this->profiler->shouldReceive('startNewSpan')
                       ->with(Mockery::type('string'))
                       ->times(3);

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
                   ->andReturn([ [ 'id' => '1' ] ]);

        $this->info->shouldReceive('setResults')
                   ->with(0, 1, 0, 0, 0)
                   ->once();

        $this->class->deleteData($delete, $this->info, TRUE);
    }

}

?>
