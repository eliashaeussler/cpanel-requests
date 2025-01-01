<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cpanel-requests".
 *
 * Copyright (C) 2020-2025 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CpanelRequests\Tests\Helper;

use EliasHaeussler\CpanelRequests as Src;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use stdClass;

/**
 * UriHelperTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Helper\UriHelper::class)]
final class UriHelperTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public static function mergePathSegmentsMergesUriPathWithGivenPathSegments(): void
    {
        $uri = new Psr7\Uri('https://www.example.com/a/long/url');
        $paths = [
            'foo',
            new stdClass(),
            null,
            5,
        ];

        self::assertSame('a/long/url/foo/5', Src\Helper\UriHelper::mergePathSegments($uri, $paths));
    }
}
