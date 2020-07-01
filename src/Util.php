<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests;

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

use Composer\Autoload\ClassLoader;

/**
 * Class providing utility functions.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class Util
{
    /**
     * Get app directory of current environment.
     *
     * Returns the app directory of the currently used environment. This should be in most
     * cases the directory containing the root `composer.json` file. Determination of this
     * directory is done by analyzing the file representing the {@see ClassLoader} class.
     * This file is used by Composer to provide an additional autoloader. It should in most
     * cases be stored in `vendor/composer/ClassLoader.php`.
     *
     * @return string App directory of current environment
     */
    public static function appDir(): string
    {
        $reflection = new \ReflectionClass(ClassLoader::class);
        return dirname($reflection->getFileName(), 3);
    }
}
