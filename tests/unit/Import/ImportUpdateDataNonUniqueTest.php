<?php

/**
 * This file contains the ImportUpdateDataNonUniqueTest class.
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
 * This class contains tests for the duplicate removal behavior in the Import class.
 */
#[CoversClass(Import::class)]
class ImportUpdateDataNonUniqueTest extends ImportTestCase
{

    /**
     * Set up common expectations for non-unique tests.
     *
     * @param int $itemCount Number of items in $new
     *
     * @return void
     */
    private function setupNonUniqueExpectations(int $itemCount): void
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
     * Test that identical duplicates are squashed (first kept, rest removed).
     */
    public function testIdenticalDuplicatesAreSquashed(): void
    {
        $new = [
            [ 'id' => '1', 'name' => 'foo' ],
            [ 'id' => '1', 'name' => 'foo' ],
            [ 'id' => '1', 'name' => 'foo' ],
        ];

        $this->setupNonUniqueExpectations(3);

        $this->target->shouldReceive('getUniqueKeys')
                     ->once()
                     ->andReturn([]);

        $this->observer->shouldReceive('getItemIdentifier')
                       ->with(Mockery::type('array'))
                       ->times(3)
                       ->andReturn('id_1', 'id_1', 'id_1');

        $this->logger->shouldReceive('warning')
                     ->with(
                         Mockery::type('string'),
                         Mockery::on(function ($context) {
                             return $context['action'] === 'Squashing'
                                 && $context['amount'] === 3
                                 && $context['index'] === 'PRIMARY';
                         })
                     )
                     ->once();

        $this->class->updateData($new, $this->info);

        $this->assertCount(1, $new);
        $this->assertSame([ 'id' => '1', 'name' => 'foo' ], $new[0]);
    }

    /**
     * Test that non-identical duplicates are all removed.
     */
    public function testNonIdenticalDuplicatesAreRemoved(): void
    {
        $new = [
            [ 'id' => '1', 'name' => 'foo' ],
            [ 'id' => '1', 'name' => 'bar' ],
        ];

        $this->setupNonUniqueExpectations(2);

        $this->target->shouldReceive('getUniqueKeys')
                     ->once()
                     ->andReturn([]);

        $this->observer->shouldReceive('getItemIdentifier')
                       ->with(Mockery::type('array'))
                       ->times(2)
                       ->andReturn('id_1', 'id_1');

        $this->logger->shouldReceive('warning')
                     ->with(
                         Mockery::type('string'),
                         Mockery::on(function ($context) {
                             return $context['action'] === 'Removing'
                                 && $context['amount'] === 2
                                 && $context['index'] === 'PRIMARY';
                         })
                     )
                     ->once();

        $this->class->updateData($new, $this->info);

        $this->assertCount(0, $new);
    }

    /**
     * Test that duplicates by unique key are detected.
     */
    public function testDuplicatesByUniqueKeyAreDetected(): void
    {
        $new = [
            [ 'id' => '1', 'name' => 'foo', 'email' => 'a@b.com' ],
            [ 'id' => '2', 'name' => 'bar', 'email' => 'a@b.com' ],
        ];

        $this->setupNonUniqueExpectations(2);

        $this->target->shouldReceive('getUniqueKeys')
                     ->once()
                     ->andReturn([
                         [ 'name' => 'unique_email', 'keys' => [ 'email' ] ],
                     ]);

        $this->observer->shouldReceive('getItemIdentifier')
                       ->with(Mockery::type('array'))
                       ->times(2)
                       ->andReturn('id_1', 'id_2');

        $this->observer->shouldReceive('getUniqueIdentifier')
                       ->with(Mockery::type('array'), [ 'email' ])
                       ->times(2)
                       ->andReturn('email_a@b.com', 'email_a@b.com');

        $this->logger->shouldReceive('warning')
                     ->with(
                         Mockery::type('string'),
                         Mockery::on(function ($context) {
                             return $context['action'] === 'Removing'
                                 && $context['amount'] === 2
                                 && $context['index'] === 'unique_email';
                         })
                     )
                     ->once();

        $this->class->updateData($new, $this->info);

        $this->assertCount(0, $new);
    }

    /**
     * Test that non-duplicate items are unchanged.
     */
    public function testNonDuplicateItemsAreUnchanged(): void
    {
        $new = [
            [ 'id' => '1', 'name' => 'foo' ],
            [ 'id' => '2', 'name' => 'bar' ],
            [ 'id' => '3', 'name' => 'baz' ],
        ];

        $this->setupNonUniqueExpectations(3);

        $this->target->shouldReceive('getUniqueKeys')
                     ->once()
                     ->andReturn([]);

        $this->observer->shouldReceive('getItemIdentifier')
                       ->with(Mockery::type('array'))
                       ->times(3)
                       ->andReturn('id_1', 'id_2', 'id_3');

        $this->logger->shouldNotReceive('warning');

        $this->class->updateData($new, $this->info);

        $this->assertCount(3, $new);
    }

}

?>
