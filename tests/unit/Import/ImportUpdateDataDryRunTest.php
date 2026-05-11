<?php

/**
 * This file contains the ImportUpdateDataDryRunTest class.
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
 * This class contains tests for the dry-run behavior of the Import class.
 */
#[CoversClass(Import::class)]
class ImportUpdateDataDryRunTest extends ImportTestCase
{

    /**
     * Set up default dry-run expectations.
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

        $this->info->shouldReceive('hasFlag')
                   ->with(Flag::IgnoreTimeKeyOnlyChange)
                   ->once()
                   ->andReturn(FALSE);

        $this->info->shouldReceive('getProfiler')
                   ->times(4)
                   ->andReturn($this->profiler);

        $this->logger->shouldReceive('notice')
                     ->with(Mockery::type('string'))
                     ->times(4);

        $this->profiler->shouldReceive('startNewSpan')
                       ->with(Mockery::type('string'))
                       ->times(4);

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
                   ->andReturn([ [ 'id' => '2' ] ]);

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
                   ->with(1, 0, 0, 0, 0)
                   ->once();
    }

    /**
     * Test that dry-run returns zero.
     */
    public function testDryRunReturnsZero(): void
    {
        $new = [ [ 'id' => '1' ] ];

        $this->setupDryRunExpectations();

        $result = $this->class->updateData($new, $this->info, TRUE);

        $this->assertSame(0, $result);
    }

    /**
     * Test that dry-run does not call target updateData.
     */
    public function testDryRunDoesNotCallTargetUpdateData(): void
    {
        $new = [ [ 'id' => '1' ] ];

        $this->setupDryRunExpectations();

        $this->target->shouldNotReceive('updateData');

        $this->class->updateData($new, $this->info, TRUE);
    }

    /**
     * Test that dry-run still sets results.
     */
    public function testDryRunStillSetsResults(): void
    {
        $new = [ [ 'id' => '1' ] ];

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
                   ->times(4)
                   ->andReturn($this->profiler);

        $this->logger->shouldReceive('notice')
                     ->with(Mockery::type('string'))
                     ->times(4);

        $this->profiler->shouldReceive('startNewSpan')
                       ->with(Mockery::type('string'))
                       ->times(4);

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
                   ->andReturn([ [ 'id' => '2' ] ]);

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
                   ->with(1, 0, 0, 0, 0)
                   ->once();

        $this->class->updateData($new, $this->info, TRUE);
    }

}

?>
