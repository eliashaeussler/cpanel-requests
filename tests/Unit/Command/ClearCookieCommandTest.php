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

use EliasHaeussler\CpanelRequests\Application\Traits\CookieAwareTrait;
use EliasHaeussler\CpanelRequests\Command\ClearCookieCommand;

/**
 * Class ClearCookieCommandTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class ClearCookieCommandTest extends AbstractCommandTest
{
    protected $commandClass = ClearCookieCommand::class;
    protected $testFileExtension = 'txt';

    /**
     * @test
     */
    public function executeClearsNoCookiesIfCookieDirectoryIsEmpty(): void
    {
        $this->clearAllFiles();

        $this->commandTester->execute(['--lifetime' => 0]);

        $this->assertClearedCookies(0);
    }

    /**
     * @test
     */
    public function executeClearsAllCookiesIfLifetimeIsZero(): void
    {
        $this->addTestFiles();

        $this->commandTester->execute(['--lifetime' => 0]);

        $this->assertClearedCookies(count($this->testFiles));
    }

    /**
     * @test
     */
    public function executeClearsOnlyExpiredCookies(): void
    {
        $this->addTestFiles();
        sleep(5);

        touch($this->testFiles[0]);

        $this->commandTester->execute(['--lifetime' => 3]);

        $this->assertClearedCookies(count($this->testFiles) - 1);
    }

    protected function assertClearedCookies(int $expected): void
    {
        static::assertStringContainsString('Cleared ' . $expected . ' cookies.', $this->commandTester->getDisplay());
    }

    protected function getTestFilePath(): string
    {
        return CookieAwareTrait::$cookieDirectory;
    }
}
