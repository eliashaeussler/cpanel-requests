<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cpanel-requests".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

use function array_pop;
use function iterator_to_array;
use function sleep;
use function touch;

/**
 * CookieTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CookieTest extends Framework\TestCase
{
    protected function setUp(): void
    {
        Resource\Cookie::removeAll(0);
    }

    #[Framework\Attributes\Test]
    public function createCreatesANewCookieFile(): void
    {
        $actual = Resource\Cookie::create();

        self::assertFileExists($actual->getPathname());
    }

    #[Framework\Attributes\Test]
    public function removeAllWithoutLifetimeRemovesAllCookieFiles(): void
    {
        $cookies = [];

        for ($i = 0; $i < 5; ++$i) {
            $cookie = Resource\Cookie::create();
            $cookies[] = $cookie;

            self::assertFileExists($cookie->getPathname());
        }

        self::assertEquals($cookies, Resource\Cookie::removeAll(0));

        /** @var Resource\File $cookie */
        foreach ($cookies as $cookie) {
            self::assertFileDoesNotExist($cookie->getPathname());
        }
    }

    #[Framework\Attributes\Test]
    public function removeAllWithLifetimeRemovesAllExpiredCookieFiles(): void
    {
        $cookies = [];

        for ($i = 0; $i < 5; ++$i) {
            $cookie = Resource\Cookie::create();
            $cookies[] = $cookie;

            self::assertFileExists($cookie->getPathname());
        }

        sleep(5);

        $existingCookie = array_pop($cookies);

        self::assertInstanceOf(Resource\File::class, $existingCookie);

        touch($existingCookie->getPathname());

        self::assertEquals($cookies, Resource\Cookie::removeAll(3));

        /** @var Resource\File $cookie */
        foreach ($cookies as $cookie) {
            self::assertFileDoesNotExist($cookie->getPathname());
        }

        self::assertFileExists($existingCookie->getPathname());
    }

    #[Framework\Attributes\Test]
    public function listAllListsAllAvailableCookieFiles(): void
    {
        $cookies = [];

        for ($i = 0; $i < 5; ++$i) {
            $cookies[] = Resource\Cookie::create();
        }

        self::assertEquals($cookies, iterator_to_array(Resource\Cookie::listAll()));
    }

    protected function tearDown(): void
    {
        Resource\Cookie::removeAll(0);
    }
}
