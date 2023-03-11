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

namespace EliasHaeussler\CpanelRequests\Command;

use EliasHaeussler\CpanelRequests\Resource;
use Symfony\Component\Console;

use function count;

/**
 * CleanupLogsCommand.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CleanupLogsCommand extends Console\Command\Command
{
    protected function configure(): void
    {
        $this
            ->setName('cleanup:logs')
            ->setDescription('Clear log files')
            ->setHelp('This command clears all log files.')
        ;
    }

    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output): int
    {
        $io = new Console\Style\SymfonyStyle($input, $output);

        $clearedLogFiles = Resource\Log::removeAll();
        $count = count($clearedLogFiles);

        if ($io->isVerbose() && $count > 0) {
            $io->writeln('The following log files have been removed:');
            $io->listing(array_map(self::decorateFile(...), $clearedLogFiles));
        }

        $io->success(sprintf('Cleared %s log file%s.', $count, 1 !== $count ? 's' : ''));

        return self::SUCCESS;
    }

    private static function decorateFile(Resource\File $file): string
    {
        return $file->getPathname();
    }
}
