<?php

/**
 * This file contains the ImportDeleteDataTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Import;

use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Pipeline\Import\Import;

/**
 * This class contains tests for the deleteData() method of the Import class.
 */
#[CoversClass(Import::class)]
class ImportDeleteDataTest extends ImportTestCase
{

    /**
     * Set up default expectations for a deleteData() call.
     *
     * @param array $delete       Delete data items
     * @param int   $targetResult Result from target updateData
     *
     * @return void
     */
    private function setupDeleteExpectations(array $delete, int $targetResult = 0): void
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
                       ->times(count($delete))
                       ->andReturn('id1');

        $this->diff->shouldReceive('diff')
                   ->with($delete, [])
                   ->once();

        $this->diff->shouldReceive('getObsoleteData')
                   ->once()
                   ->andReturn($delete);

        $this->info->shouldReceive('setResults')
                   ->with(0, count($delete), 0, 0, 0)
                   ->once();

        $this->target->shouldReceive('updateData')
                     ->with(Mockery::type('array'), [])
                     ->once()
                     ->andReturn($targetResult);

        $this->logger->shouldReceive('warning');
    }

    /**
     * Test that deleteData() calls setTarget with the target identifier.
     */
    public function testDeleteDataSetsTarget(): void
    {
        $delete = [ [ 'id' => '1', 'name' => 'foo' ] ];

        $this->setupDeleteExpectations($delete);

        $this->class->deleteData($delete, $this->info);
    }

    /**
     * Test that deleteData() calls diff with delete items as old data and empty array as new data.
     */
    public function testDeleteDataCallsDiffWithDeleteItemsAndEmptyArray(): void
    {
        $delete = [
            [ 'id' => '1', 'name' => 'foo' ],
            [ 'id' => '2', 'name' => 'bar' ],
        ];

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
                       ->twice()
                       ->andReturn('id1', 'id2');

        $this->diff->shouldReceive('diff')
                   ->with($delete, [])
                   ->once();

        $this->diff->shouldReceive('getObsoleteData')
                   ->once()
                   ->andReturn($delete);

        $this->info->shouldReceive('setResults')
                   ->with(0, 2, 0, 0, 0)
                   ->once();

        $this->target->shouldReceive('updateData')
                     ->with(Mockery::type('array'), [])
                     ->once()
                     ->andReturn(0);

        $this->logger->shouldReceive('warning');

        $this->class->deleteData($delete, $this->info);
    }

    /**
     * Test that deleteData() passes correct data structure to target.
     */
    public function testDeleteDataPassesCorrectDataStructureToTarget(): void
    {
        $delete = [
            [ 'id' => '1', 'name' => 'foo' ],
            [ 'id' => '2', 'name' => 'bar' ],
        ];

        $expectedData = [
            'new'      => [],
            'updated'  => [],
            'obsolete' => $delete,
        ];

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
                       ->twice()
                       ->andReturn('id1', 'id2');

        $this->diff->shouldReceive('diff')
                   ->with($delete, [])
                   ->once();

        $this->diff->shouldReceive('getObsoleteData')
                   ->once()
                   ->andReturn($delete);

        $this->info->shouldReceive('setResults')
                   ->with(0, 2, 0, 0, 0)
                   ->once();

        $this->target->shouldReceive('updateData')
                     ->with($expectedData, [])
                     ->once()
                     ->andReturn(2);

        $this->class->deleteData($delete, $this->info);
    }

    /**
     * Test that deleteData() returns the target result.
     */
    public function testDeleteDataReturnsTargetResult(): void
    {
        $delete = [ [ 'id' => '1', 'name' => 'foo' ] ];

        $this->setupDeleteExpectations($delete, 20);

        $result = $this->class->deleteData($delete, $this->info);

        $this->assertSame(20, $result);
    }

}

?>
