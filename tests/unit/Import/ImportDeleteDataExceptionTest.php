<?php

/**
 * This file contains the ImportDeleteDataExceptionTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2026 Framna Netherlands B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Pipeline\Tests\Import;

use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Pipeline\Import\Exceptions\DatabaseException;
use Pipeline\Import\Import;

/**
 * This class contains tests for DatabaseException handling in the deleteData() method.
 */
#[CoversClass(Import::class)]
class ImportDeleteDataExceptionTest extends ImportTestCase
{

    /**
     * Test that DatabaseException from updateData is rethrown with a new message.
     */
    public function testDatabaseExceptionFromUpdateDataIsRethrownWithNewMessage(): void
    {
        $delete = [ [ 'id' => '1', 'name' => 'foo' ] ];

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
                   ->andReturn($delete);

        $this->info->shouldReceive('setResults')
                   ->with(0, 1, 0, 0, 0)
                   ->once();

        $exception = new DatabaseException('Original error');

        $this->target->shouldReceive('updateData')
                     ->with(Mockery::type('array'), [])
                     ->once()
                     ->andThrow($exception);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Failed updating data in the database!');

        $this->class->deleteData($delete, $this->info);
    }

}

?>
