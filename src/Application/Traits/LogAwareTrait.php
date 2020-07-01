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
 * LogAwareTrait
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
trait LogAwareTrait
{
    /**
     * @var string Directory where to store the log files
     */
    public static $logDirectory = 'temp' . DIRECTORY_SEPARATOR . 'logs';

    /**
     * @var string File name for request logs
     */
    public static $logFileName = 'requests.log';

    /**
     * @var File File containing request logs
     */
    protected $logFile;

    /**
     * Generate request log file.
     */
    protected function generateLogFile(): void
    {
        $pathname = Util::appDir() . DIRECTORY_SEPARATOR . static::$logDirectory . DIRECTORY_SEPARATOR . static::$logFileName;
        $file = new File($pathname);

        // Create log file
        if (!$file->create()) {
            throw new IOException(sprintf('File "%s" could not be created.', $file->getPathname()), 1589838591);
        }

        // Store log file
        $this->logFile = $file;
    }
}
