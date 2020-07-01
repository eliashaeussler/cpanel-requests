<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\Tests\Unit\Command;

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
use EliasHaeussler\CpanelRequests\Command\ClearLogFileCommand;

/**
 * Class ClearLogFileCommandTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class ClearLogFileCommandTest extends AbstractCommandTest
{
    protected $commandClass = ClearLogFileCommand::class;
    protected $testFileExtension = 'log';

    /**
     * @test
     */
    public function executeClearsNoLogFilesIfLogDirectoryIsEmpty(): void
    {
        $this->clearAllFiles();

        $this->commandTester->execute([]);

        $this->assertClearedLogFiles(0);
    }

    /**
     * @test
     */
    public function executeClearsAllLogFiles(): void
    {
        $this->addTestFiles();

        $this->commandTester->execute([]);

        $this->assertClearedLogFiles(count($this->testFiles));
    }

    protected function assertClearedLogFiles(int $expected): void
    {
        static::assertStringContainsString('Cleared ' . $expected . ' log files.', $this->commandTester->getDisplay());
    }

    protected function getTestFilePath(): string
    {
        return LogAwareTrait::$logDirectory;
    }
}
