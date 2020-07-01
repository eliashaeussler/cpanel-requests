<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\Command;

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

use EliasHaeussler\CpanelRequests\Application\Traits\LogAwareTrait;
use EliasHaeussler\CpanelRequests\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * `clear:logfile` console command.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class ClearLogFileCommand extends Command
{
    protected static $defaultName = 'clear:logfile';

    public function getDescription(): string
    {
        return 'Clear log files';
    }

    public function getHelp(): string
    {
        return 'This command clears all log files.';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clearedLogFiles = $this->clearLogs();
        $output->writeln(
            sprintf('<info>Cleared %s log file%s.</info>', $clearedLogFiles, $clearedLogFiles !== 1 ? 's' : '')
        );
    }

    /**
     * Clear log files.
     *
     * @return int Number of cleared log files
     */
    protected function clearLogs(): int
    {
        $logFiles = Finder::create()
            ->files()
            ->in(Util::appDir())
            ->path(LogAwareTrait::$logDirectory)->name('*.log');

        $clearedLogFiles = 0;
        foreach ($logFiles as $file) {
            if (@unlink($file->getPathname())) {
                $clearedLogFiles++;
            }
        }

        return $clearedLogFiles;
    }
}
