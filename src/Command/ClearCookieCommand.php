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

use EliasHaeussler\CpanelRequests\Application\Traits\CookieAwareTrait;
use EliasHaeussler\CpanelRequests\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * `clear:cookie` console command.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class ClearCookieCommand extends Command
{
    private const DEFAULT_LIFETIME = 604800;

    protected static $defaultName = 'clear:cookie';

    public function getDescription(): string
    {
        return 'Clear cookie files';
    }

    public function getHelp(): string
    {
        return 'This command clears cookie files whose lifetime has expired.';
    }

    protected function configure()
    {
        $this->addOption(
            'lifetime',
            'l',
            InputOption::VALUE_OPTIONAL,
            'Lifetime of cookie files in seconds',
            self::DEFAULT_LIFETIME
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lifetime = (int) $input->getOption('lifetime');
        $clearedCookies = $this->clearCookies($lifetime);
        $output->writeln(
            sprintf('<info>Cleared %d cookie%s.</info>', $clearedCookies, $clearedCookies !== 1 ? 's' : '')
        );
    }

    /**
     * Clear expired cookie files by given lifetime.
     *
     * @param int $lifetime Lifetime of cookie files in seconds (defaults to 7 days)
     * @return int Number of cleared cookie files
     */
    private function clearCookies(int $lifetime = self::DEFAULT_LIFETIME): int
    {
        $targetTime = time() - $lifetime;
        $cookieFiles = Finder::create()
            ->files()
            ->in(Util::appDir())
            ->path(CookieAwareTrait::$cookieDirectory)
            ->name('*.txt')
            ->date('<= @' . $targetTime);

        $clearedCookies = 0;
        foreach ($cookieFiles as $file) {
            if (@unlink($file->getPathname())) {
                $clearedCookies++;
            }
        }
        return $clearedCookies;
    }
}
