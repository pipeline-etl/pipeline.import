<?php

/**
 * This file contains the ImportUpdateDataContentRangeTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Import;

use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Pipeline\Common\Node;
use Pipeline\Import\ContentRangeInterface;
use Pipeline\Import\Flag;
use Pipeline\Import\Import;

/**
 * This class contains tests for the content range configuration in the Import class.
 */
#[CoversClass(Import::class)]
class ImportUpdateDataContentRangeTest extends ImportTestCase
{

    /**
     * Test that content range is resolved and configured.
     */
    public function testContentRangeResolvedAndConfigured(): void
    {
        $new    = [ [ 'id' => '1', 'name' => 'foo' ] ];
        $config = [ 'key' => 'value' ];

        $range = Mockery::mock(ContentRangeInterface::class . ', ' . Node::class);

        $this->info->shouldReceive('getTargetIdentifier')
                   ->once()
                   ->andReturn('target_name');

        $this->target->shouldReceive('setTarget')
                     ->with('target_name')
                     ->once();

        $this->info->shouldReceive('getContentRanges')
                   ->once()
                   ->andReturn([ [ 'myRange' => $config ] ]);

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

        $this->locator->shouldReceive('getContentRange')
                      ->with('myRange')
                      ->once()
                      ->andReturn($range);

        $range->shouldReceive('setData')
              ->with(Mockery::type('array'), $config)
              ->once();

        $this->target->shouldReceive('getData')
                     ->with([ 'id', 'name' ], [ $range ])
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
                     ->with(Mockery::type('array'), [ $range ])
                     ->once()
                     ->andReturn(0);

        $this->class->updateData($new, $this->info);
    }

    /**
     * Test that content range is skipped when locator returns null.
     */
    public function testContentRangeSkippedWhenLocatorReturnsNull(): void
    {
        $new = [ [ 'id' => '1', 'name' => 'foo' ] ];

        $this->info->shouldReceive('getTargetIdentifier')
                   ->once()
                   ->andReturn('target_name');

        $this->target->shouldReceive('setTarget')
                     ->with('target_name')
                     ->once();

        $this->info->shouldReceive('getContentRanges')
                   ->once()
                   ->andReturn([ [ 'myRange' => [ 'key' => 'value' ] ] ]);

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

        $this->locator->shouldReceive('getContentRange')
                      ->with('myRange')
                      ->once()
                      ->andReturn(NULL);

        $this->target->shouldReceive('getData')
                     ->with([ 'id', 'name' ], [])
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

        $result = $this->class->updateData($new, $this->info);

        $this->assertSame(0, $result);
    }

    /**
     * Test with empty content ranges.
     */
    public function testEmptyContentRanges(): void
    {
        $new = [ [ 'id' => '1' ] ];

        $this->setupDefaultExpectations($new);

        $this->locator->shouldNotReceive('getContentRange');

        $this->class->updateData($new, $this->info);
    }

}

?>
