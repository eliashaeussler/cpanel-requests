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

namespace EliasHaeussler\CpanelRequests\Tests\Command;

use EliasHaeussler\CpanelRequests\Command;
use EliasHaeussler\CpanelRequests\Resource;
use PHPUnit\Framework;
use Symfony\Component\Console;

/**
 * CleanupLogsCommandTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CleanupLogsCommandTest extends Framework\TestCase
{
    private Console\Tester\CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->commandTester = new Console\Tester\CommandTester(new Command\CleanupLogsCommand());

        Resource\Log::removeAll();
    }

    /**
     * @test
     */
    public function executeClearsNoLogFilesIfLogDirectoryIsEmpty(): void
    {
        $this->commandTester->execute([]);

        $this->assertNumberOfClearedLogFiles(0);
    }

    /**
     * @test
     */
    public function executeClearsAllLogFiles(): void
    {
        $this->createLogFiles();

        $this->commandTester->execute([]);

        $this->assertNumberOfClearedLogFiles(5);
    }

    /**
     * @return list<Resource\File>
     */
    private function createLogFiles(int $number = 5): array
    {
        $logFiles = [];

        for ($i = 0; $i < $number; ++$i) {
            $logFiles[] = Resource\Log::create();
        }

        return $logFiles;
    }

    /**
     * @test
     */
    public function executeListsAllRemovedCookiesIfOutputIsVerbose(): void
    {
        $logFiles = self::createLogFiles();

        $this->commandTester->execute([], ['verbosity' => Console\Output\OutputInterface::VERBOSITY_VERBOSE]);

        $output = $this->commandTester->getDisplay();

        foreach ($logFiles as $logFile) {
            self::assertStringContainsString($logFile->getPathname(), $output);
        }
    }

    private function assertNumberOfClearedLogFiles(int $expected): void
    {
        self::assertStringContainsString('Cleared '.$expected.' log files.', $this->commandTester->getDisplay());
    }
}
