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

namespace EliasHaeussler\CpanelRequests\Tests\Http\UriBuilder;

use EliasHaeussler\CpanelRequests\Http;
use Generator;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Psr\Http\Message;

/**
 * DefaultUriBuilderTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class DefaultUriBuilderTest extends Framework\TestCase
{
    protected Http\UriBuilder\DefaultUriBuilder $subject;

    protected function setUp(): void
    {
        $this->subject = new Http\UriBuilder\DefaultUriBuilder();
    }

    /**
     * @test
     *
     * @dataProvider buildUriForRequestAppliesModuleAndFunctionToBaseUriDataProvider
     */
    public function buildUriForRequestAppliesModuleAndFunctionToBaseUri(
        Message\UriInterface $baseUri,
        string $expected
    ): void {
        $request = new Http\Request\ApiRequest($baseUri, 'foo', 'bar', ['hello' => 'world']);

        $actual = $this->subject->buildUriForRequest($request);

        self::assertSame($expected, (string) $actual);
    }

    /**
     * @return Generator<string, array{Message\UriInterface, string}>
     */
    public function buildUriForRequestAppliesModuleAndFunctionToBaseUriDataProvider(): Generator
    {
        $baseUri = new Psr7\Uri('http://example.org');

        yield 'protocol and host only' => [$baseUri, 'http://example.org/foo/bar?hello=world'];
        yield 'with additional base path' => [$baseUri->withPath('/dummy'), 'http://example.org/dummy/foo/bar?hello=world'];
        yield 'with additional query params' => [$baseUri->withQuery('foo=baz'), 'http://example.org/foo/bar?foo=baz&hello=world'];
        yield 'with additional fragment' => [$baseUri->withFragment('boo'), 'http://example.org/foo/bar?hello=world#boo'];
    }
}
