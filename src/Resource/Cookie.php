<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cpanel-requests".
 *
 * Copyright (C) 2020-2024 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CpanelRequests\Resource;

use EliasHaeussler\CpanelRequests\Helper;
use Generator;
use Symfony\Component\Filesystem;
use Symfony\Component\Finder;

/**
 * Cookie.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class Cookie
{
    // 1 hour
    public const DEFAULT_LIFETIME = 3600;

    private const COOKIE_DIRECTORY = 'temp/cookies';

    private function __construct() {}

    public static function create(): File
    {
        // Generate unique cookie identifier
        do {
            $identifier = uniqid('cookie_', true);
            $file = self::buildCookieFile($identifier);
        } while ($file->exists());

        // Create cookie file
        $file->create();

        return $file;
    }

    /**
     * @return list<File>
     */
    public static function removeAll(int $lifetime = self::DEFAULT_LIFETIME): array
    {
        $targetTime = time() - $lifetime;
        $cookieFiles = self::createFinder()
            ->date('<= @'.$targetTime)
        ;

        $clearedCookieFiles = [];

        foreach ($cookieFiles as $cookieFile) {
            $file = new File($cookieFile->getPathname());
            $file->remove();

            $clearedCookieFiles[] = $file;
        }

        return $clearedCookieFiles;
    }

    /**
     * @return Generator<File>
     */
    public static function listAll(): Generator
    {
        foreach (self::createFinder() as $cookieFile) {
            yield new File($cookieFile->getPathname());
        }
    }

    private static function createFinder(): Finder\Finder
    {
        return Finder\Finder::create()
            ->files()
            ->in(Filesystem\Path::join(Helper\FilesystemHelper::getTemporaryStorageDirectory(), self::COOKIE_DIRECTORY))
            ->name('cookie_*.txt')
            ->depth('== 0')
        ;
    }

    private static function buildCookieFile(string $identifier): File
    {
        $filename = sprintf('%s.txt', $identifier);
        $pathname = Filesystem\Path::join(
            Helper\FilesystemHelper::getTemporaryStorageDirectory(),
            self::COOKIE_DIRECTORY,
            $filename,
        );

        return new File($pathname);
    }
}
