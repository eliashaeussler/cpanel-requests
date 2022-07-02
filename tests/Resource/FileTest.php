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

namespace EliasHaeussler\CpanelRequests\Tests\Resource;

use EliasHaeussler\CpanelRequests\Resource;
use PHPUnit\Framework;
use Symfony\Component\Filesystem;
use function sys_get_temp_dir;

/**
 * FileTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class FileTest extends Framework\TestCase
{
    private string $file;
    private Resource\File $subject;

    protected function setUp(): void
    {
        $this->file = (new Filesystem\Filesystem())->tempnam(sys_get_temp_dir(), 'cpanel_requests_', '.txt');
        $this->subject = new Resource\File($this->file);
    }

    /**
     * @test
     */
    public function existsReturnStateOfExistence(): void
    {
        $this->subject->remove();

        self::assertFalse($this->subject->exists());
        self::assertFileDoesNotExist($this->file);

        $this->subject->create();

        self::assertTrue($this->subject->exists());
        self::assertFileExists($this->file);

        $this->subject->create();

        self::assertTrue($this->subject->exists());
        self::assertFileExists($this->file);
    }

    protected function tearDown(): void
    {
        $this->subject->remove();
    }
}
