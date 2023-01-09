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

namespace EliasHaeussler\CpanelRequests\Tests\Command;

use EliasHaeussler\CpanelRequests\Command;
use EliasHaeussler\CpanelRequests\Resource;
use PHPUnit\Framework;
use Symfony\Component\Console;

use function sleep;
use function touch;

/**
 * CleanupCookiesCommandTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CleanupCookiesCommandTest extends Framework\TestCase
{
    private Console\Tester\CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->commandTester = new Console\Tester\CommandTester(new Command\CleanupCookiesCommand());

        Resource\Cookie::removeAll(0);
    }

    /**
     * @test
     */
    public function executeClearsNoCookiesIfCookieDirectoryIsEmpty(): void
    {
        $this->commandTester->execute(['--lifetime' => 0]);

        $this->assertNumberOfClearedCookies(0);
    }

    /**
     * @test
     */
    public function executeClearsAllCookiesIfLifetimeIsZero(): void
    {
        self::createCookies();

        $this->commandTester->execute(['--lifetime' => 0]);

        $this->assertNumberOfClearedCookies(5);
    }

    /**
     * @test
     */
    public function executeClearsOnlyExpiredCookies(): void
    {
        [$firstCookieFile] = self::createCookies();

        sleep(5);

        touch($firstCookieFile->getPathname());

        $this->commandTester->execute(['--lifetime' => 3]);

        $this->assertNumberOfClearedCookies(4);
    }

    /**
     * @test
     */
    public function executeListsAllRemovedCookiesIfOutputIsVerbose(): void
    {
        $cookies = self::createCookies();

        $this->commandTester->execute(
            ['--lifetime' => 0],
            ['verbosity' => Console\Output\OutputInterface::VERBOSITY_VERBOSE]
        );

        $output = $this->commandTester->getDisplay();

        foreach ($cookies as $cookieFile) {
            self::assertStringContainsString($cookieFile->getPathname(), $output);
        }
    }

    /**
     * @test
     */
    public function executeFallsBackToDefaultLifetimeIfGivenLifetimeIsNotNumeric(): void
    {
        self::createCookies(1);

        $this->commandTester->execute(['--lifetime' => 'foo']);

        $this->assertNumberOfClearedCookies(0);
    }

    /**
     * @return list<Resource\File>
     */
    private function createCookies(int $number = 5): array
    {
        $cookieFiles = [];

        for ($i = 0; $i < $number; ++$i) {
            $cookieFiles[] = Resource\Cookie::create();
        }

        return $cookieFiles;
    }

    private function assertNumberOfClearedCookies(int $expected): void
    {
        self::assertStringContainsString('Cleared '.$expected.' cookies.', $this->commandTester->getDisplay());
    }
}
