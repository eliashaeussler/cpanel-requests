<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\Application\Traits;

/*
 * This file is part of the Composer package "eliashaeussler/cpanel-requests".
 *
 * Copyright (C) 2020 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\CpanelRequests\Exception\IOException;
use EliasHaeussler\CpanelRequests\IO\File;
use EliasHaeussler\CpanelRequests\Util;

/**
 * CookieAwareTrait
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
trait CookieAwareTrait
{
    /**
     * @var string Directory where to store the request cookies
     */
    public static $cookieDirectory = 'temp' . DIRECTORY_SEPARATOR . 'cookies';

    /**
     * @var File File containing current session cookie
     */
    protected $cookieFile;

    /**
     * Generate identifier for cookie file.
     *
     * @throws IOException if cookie file cannot be created
     */
    protected function generateCookieIdentifier(): void
    {
        // Generate cookie identifier
        do {
            $identifier = uniqid('', true);
            $file = $this->buildCookieFileName($identifier);
        } while ($file->exists());

        // Create cookie file
        if (!$file->create()) {
            throw new IOException(sprintf('File "%s" could not be created.', $file->getPathname()), 1589838271);
        }

        // Store cookie identifier
        $this->cookieFile = $file;
    }

    /**
     * Build cookie file by given identifier.
     *
     * @param string $identifier Identifier for cookie file
     * @return File Cookie file
     */
    protected function buildCookieFileName(string $identifier): File
    {
        $filename = sprintf('cookie_%s.txt', $identifier);
        $pathname = Util::appDir() . DIRECTORY_SEPARATOR . static::$cookieDirectory . DIRECTORY_SEPARATOR . $filename;
        return new File($pathname);
    }

    /**
     * Remove previously generated cookie file.
     */
    protected function removeCookieFile(): void
    {
        $this->cookieFile->remove();
    }
}
