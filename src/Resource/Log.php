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
 * Log.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class Log
{
    private const LOG_DIRECTORY = 'temp/logs';

    private function __construct() {}

    public static function create(): File
    {
        // Generate unique log file identifier
        do {
            $identifier = uniqid('cpanel_requests_', true);
            $file = self::buildLogFile($identifier);
        } while ($file->exists());

        // Create log file
        $file->create();

        return $file;
    }

    /**
     * @return list<File>
     */
    public static function removeAll(): array
    {
        $clearedLogFiles = [];

        foreach (self::listAll() as $logFile) {
            $logFile->remove();

            $clearedLogFiles[] = $logFile;
        }

        return $clearedLogFiles;
    }

    /**
     * @return Generator<File>
     */
    public static function listAll(): Generator
    {
        foreach (self::createFinder() as $logFile) {
            yield new File($logFile->getPathname());
        }
    }

    private static function createFinder(): Finder\Finder
    {
        return Finder\Finder::create()
            ->files()
            ->in(Filesystem\Path::join(Helper\FilesystemHelper::getTemporaryStorageDirectory(), self::LOG_DIRECTORY))
            ->name('cpanel_requests_*.log')
            ->depth('== 0')
        ;
    }

    private static function buildLogFile(string $identifier): File
    {
        $filename = sprintf('%s.log', $identifier);
        $pathname = Filesystem\Path::join(
            Helper\FilesystemHelper::getTemporaryStorageDirectory(),
            self::LOG_DIRECTORY,
            $filename,
        );

        return new File($pathname);
    }
}
