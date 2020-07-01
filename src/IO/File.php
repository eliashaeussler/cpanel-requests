<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\IO;

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

/**
 * Local file abstraction layer.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class File extends \SplFileInfo
{
    public function exists(): bool
    {
        return file_exists($this->getPathname());
    }

    public function create(): bool
    {
        if ($this->exists()) {
            return true;
        }
        if ($this->createDirectory()) {
            return touch($this->getPathname());
        }
        return false;
    }

    public function remove(): bool
    {
        if (!$this->exists()) {
            return true;
        }
        return unlink($this->getPathname());
    }

    private function createDirectory(): bool
    {
        if (@is_dir($this->getPath())) {
            return true;
        }
        $fullPath = '';
        foreach (explode(DIRECTORY_SEPARATOR, $this->getPath()) as $currentPath) {
            $fullPath .= DIRECTORY_SEPARATOR . $currentPath;
            if (is_dir($fullPath)) {
                continue;
            }
            $result = @mkdir($fullPath, 0777, true);
            if (!$result) {
                return false;
            }
        }
        return true;
    }
}
