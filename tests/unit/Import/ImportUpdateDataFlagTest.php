<?php

/**
 * This file contains the ImportUpdateDataFlagTest class.
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
 * This class contains tests for the IgnoreTimeKeyOnlyChange flag in the Import class.
 */
#[CoversClass(Import::class)]
class ImportUpdateDataFlagTest extends ImportTestCase
{

    /**
     * Set up common expectations for flag tests.
     *
     * @param bool $hasFlag Whether the flag is set
     *
     * @return void
     */
    private function setupFlagExpectations(bool $hasFlag): void
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
                   ->andReturn($hasFlag);

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

    /**
     * Test that IgnoreTimeKeyOnlyChange flag calls skipTimeKeyOnlyChange.
     */
    public function testIgnoreTimeKeyFlagCallsSkipTimeKeyOnlyChange(): void
    {
        $new = [ [ 'id' => '1' ] ];

        $this->setupFlagExpectations(TRUE);

        $this->diff->shouldReceive('skipTimeKeyOnlyChange')
                   ->with(TRUE)
                   ->once();

        $this->class->updateData($new, $this->info);
    }

    /**
     * Test that without the flag, skipTimeKeyOnlyChange is not called.
     */
    public function testWithoutFlagDoesNotCallSkipTimeKeyOnlyChange(): void
    {
        $new = [ [ 'id' => '1' ] ];

        $this->setupFlagExpectations(FALSE);

        $this->diff->shouldNotReceive('skipTimeKeyOnlyChange');

        $this->class->updateData($new, $this->info);
    }

}

?>
