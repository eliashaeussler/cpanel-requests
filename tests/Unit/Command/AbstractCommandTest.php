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

use EliasHaeussler\CpanelRequests\Util;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Finder\Finder;

/**
 * Class AbstractCommandTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
abstract class AbstractCommandTest extends TestCase
{
    /**
     * @var string
     */
    protected $commandClass = null;

    /**
     * @var string
     */
    protected $testFileExtension = null;

    /**
     * @var array
     */
    protected $testFiles = [];

    /**
     * @var CommandTester
     */
    protected $commandTester;

    protected function setUp(): void
    {
        /** @var Command $command */
        $command = new $this->commandClass();
        $application = new Application();
        $application->add($command);

        $command = $application->find($command->getDefaultName());
        $this->commandTester = new CommandTester($command);
    }

    protected function addTestFiles(): void
    {
        // Remove all files first
        $this->clearAllFiles();

        // Build test files
        $basePath = Util::appDir() . DIRECTORY_SEPARATOR . $this->getTestFilePath();
        for ($i = 0; $i < 5; $i++) {
            $testFile = uniqid('test-' . $i . '-', true) . '.' . $this->testFileExtension;
            $pathname = $basePath . DIRECTORY_SEPARATOR . $testFile;
            if (touch($pathname)) {
                $this->testFiles[] = $pathname;
            }
        }
    }

    protected function clearAllFiles(): void
    {
        $cookieFiles = Finder::create()->in(Util::appDir())->path($this->getTestFilePath());
        foreach ($cookieFiles as $cookieFile) {
            @unlink($cookieFile->getPathname());
        }
        $this->testFiles = [];
    }

    abstract protected function getTestFilePath(): string;

    protected function tearDown(): void
    {
        foreach ($this->testFiles as $testFile) {
            @unlink($testFile);
        }
    }
}
