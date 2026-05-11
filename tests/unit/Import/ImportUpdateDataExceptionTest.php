<?php

/**
 * This file contains the ImportUpdateDataExceptionTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Import;

use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Pipeline\Import\Exceptions\DatabaseException;
use Pipeline\Import\Flag;
use Pipeline\Import\Import;

/**
 * This class contains tests for DatabaseException handling in the Import class.
 */
#[CoversClass(Import::class)]
class ImportUpdateDataExceptionTest extends ImportTestCase
{

    /**
     * Test that DatabaseException from updateData is rethrown with a new message.
     */
    public function testDatabaseExceptionFromUpdateDataIsRethrownWithNewMessage(): void
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

        $exception = new DatabaseException('Original error');

        $this->target->shouldReceive('updateData')
                     ->with(Mockery::type('array'), [])
                     ->once()
                     ->andThrow($exception);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Failed updating data in the database!');

        $this->class->updateData($new, $this->info);
    }

    /**
     * Test that DatabaseException from getData is rethrown with a new message.
     */
    public function testDatabaseExceptionFromGetDataIsRethrownWithNewMessage(): void
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
                   ->andReturn([]);

        $this->info->shouldReceive('getProfiler')
                   ->times(2)
                   ->andReturn($this->profiler);

        $this->logger->shouldReceive('notice')
                     ->with(Mockery::type('string'))
                     ->times(2);

        $this->profiler->shouldReceive('startNewSpan')
                       ->with(Mockery::type('string'))
                       ->times(2);

        $exception = new DatabaseException('Original error');

        $this->target->shouldReceive('getData')
                     ->with([ 'id', 'name' ], [])
                     ->once()
                     ->andThrow($exception);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error fetching current information from the database!');

        $this->class->updateData($new, $this->info);
    }

    /**
     * Test that DatabaseException from getData prevents remaining execution.
     */
    public function testDatabaseExceptionFromGetDataPreventsRemainingExecution(): void
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
                   ->andReturn([]);

        $this->info->shouldReceive('getProfiler')
                   ->times(2)
                   ->andReturn($this->profiler);

        $this->logger->shouldReceive('notice')
                     ->with(Mockery::type('string'))
                     ->times(2);

        $this->profiler->shouldReceive('startNewSpan')
                       ->with(Mockery::type('string'))
                       ->times(2);

        $exception = new DatabaseException('Original error');

        $this->target->shouldReceive('getData')
                     ->with([ 'id', 'name' ], [])
                     ->once()
                     ->andThrow($exception);

        $this->diff->shouldNotReceive('diff');
        $this->target->shouldNotReceive('updateData');

        $this->expectException(DatabaseException::class);

        $this->class->updateData($new, $this->info);
    }

}

?>
