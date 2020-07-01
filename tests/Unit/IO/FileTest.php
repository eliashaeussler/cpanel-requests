<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\Tests\Unit\IO;

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

use EliasHaeussler\CpanelRequests\IO\File;
use EliasHaeussler\CpanelRequests\Util;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

/**
 * Class FileTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class FileTest extends TestCase
{
    protected const TEST_DIRECTORY = 'temp' . DIRECTORY_SEPARATOR . 'tests';

    /**
     * @var File
     */
    protected $subject;

    protected function setUp(): void
    {
        $file = uniqid() . '.txt';
        $this->subject = new File(Util::appDir() . DIRECTORY_SEPARATOR . self::TEST_DIRECTORY . DIRECTORY_SEPARATOR . $file);
    }

    /**
     * @test
     */
    public function existsReturnStateOfExistence(): void
    {
        $this->subject->remove();
        static::assertFalse($this->subject->exists());

        $this->subject->create();
        static::assertTrue($this->subject->exists());
    }

    /**
     * @test
     */
    public function createCanCreateFileInFileSystem(): void
    {
        // Ensure test directory is not available in file system
        $this->cleanTestDirectory();

        static::assertFalse($this->subject->exists());
        static::assertTrue($this->subject->create());
        static::assertTrue($this->subject->exists());
        static::assertTrue($this->subject->create());
        static::assertTrue($this->subject->exists());
    }

    /**
     * @test
     */
    public function removeCanRemoveFileInFileSystem(): void
    {
        $this->subject->create();

        static::assertTrue($this->subject->exists());
        static::assertTrue($this->subject->remove());
        static::assertFalse($this->subject->exists());
    }

    protected function cleanTestDirectory(): void
    {
        if (!is_dir($this->subject->getPath())) {
            return;
        }
        foreach (Finder::create()->files()->in($this->subject->getPath()) as $file) {
            unlink($file->getPathname());
        }
        rmdir($this->subject->getPath());
    }
}
