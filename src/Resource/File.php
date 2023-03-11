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

namespace EliasHaeussler\CpanelRequests\Resource;

use SplFileInfo;
use Symfony\Component\Filesystem;

/**
 * Local file abstraction layer.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class File extends SplFileInfo
{
    private readonly Filesystem\Filesystem $filesystem;

    public function __construct(string $filename)
    {
        parent::__construct($filename);
        $this->filesystem = new Filesystem\Filesystem();
    }

    public function exists(): bool
    {
        return $this->filesystem->exists($this->getPathname());
    }

    public function create(): void
    {
        if ($this->exists()) {
            return;
        }

        $this->filesystem->mkdir($this->getPath());
        $this->filesystem->touch($this->getPathname());
    }

    public function remove(): void
    {
        $this->filesystem->remove($this->getPathname());
    }
}
