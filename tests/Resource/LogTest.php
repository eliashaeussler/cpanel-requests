<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cpanel-requests".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CpanelRequests\Tests\Resource;

use EliasHaeussler\CpanelRequests\Resource;
use PHPUnit\Framework;
use function iterator_to_array;

/**
 * LogTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class LogTest extends Framework\TestCase
{
    protected function setUp(): void
    {
        Resource\Log::removeAll();
    }

    /**
     * @test
     */
    public function createCreatesANewLogFile(): void
    {
        $actual = Resource\Log::create();

        self::assertFileExists($actual->getPathname());
    }

    /**
     * @test
     */
    public function removeAllAllLogFiles(): void
    {
        $logs = [];

        for ($i = 0; $i < 5; ++$i) {
            $log = Resource\Log::create();
            $logs[] = $log;

            self::assertFileExists($log->getPathname());
        }

        self::assertEquals($logs, Resource\Log::removeAll());

        /** @var Resource\File $log */
        foreach ($logs as $log) {
            self::assertFileDoesNotExist($log->getPathname());
        }
    }

    /**
     * @test
     */
    public function listAllListsAllAvailableLogFiles(): void
    {
        $logs = [];

        for ($i = 0; $i < 5; ++$i) {
            $logs[] = Resource\Log::create();
        }

        self::assertEquals($logs, iterator_to_array(Resource\Log::listAll()));
    }

    protected function tearDown(): void
    {
        Resource\Log::removeAll();
    }
}
