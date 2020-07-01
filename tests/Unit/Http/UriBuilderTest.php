<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\Tests\Unit\Http;

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

use EliasHaeussler\CpanelRequests\Http\UriBuilder;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

/**
 * Class UriBuilderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class UriBuilderTest extends TestCase
{
    /**
     * @var UriBuilder
     */
    protected $subject;

    protected function setUp(): void
    {
        $baseUri = new Uri('http://example.org/dummy?foo=baz#boo');
        $this->subject = new UriBuilder($baseUri);
    }

    /**
     * @test
     * @dataProvider withPathAppliesComponentsFromGivenUriObjectDataProvider
     * @param UriInterface $path
     * @param string $expected
     */
    public function withPathAppliesComponentsFromUriObject(UriInterface $path, string $expected): void
    {
        static::assertSame($expected, (string)$this->subject->withPath($path));
    }

    /**
     * @test
     * @dataProvider withPathAppliesComponentsFromStringDataProvider
     * @param string $path
     * @param string $expected
     */
    public function withPathAppliesComponentsFromString(string $path, string $expected): void
    {
        static::assertSame($expected, (string)$this->subject->withPath($path));
    }

    /**
     * @test
     */
    public function withPathThrowsExceptionIfPathIsNeitherUriObjectNorString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1592848070);

        $this->subject->withPath(null);
    }

    public function withPathAppliesComponentsFromGivenUriObjectDataProvider()
    {
        return [
            'no path' => [
                new Uri('http://example.org'),
                'http://example.org?foo=baz#boo',
            ],
            'path only' => [
                new Uri('http://example.org/hello/world'),
                'http://example.org/hello/world?foo=baz#boo',
            ],
            'path and query' => [
                new Uri('http://example.org/hello/world?another=foo'),
                'http://example.org/hello/world?another=foo#boo',
            ],
            'path and fragment' => [
                new Uri('http://example.org/hello/world#whats-going-on'),
                'http://example.org/hello/world?foo=baz#whats-going-on',
            ],
            'all components' => [
                new Uri('http://example.org/hello/world?another=foo#whats-going-on'),
                'http://example.org/hello/world?another=foo#whats-going-on',
            ],
        ];
    }

    public function withPathAppliesComponentsFromStringDataProvider(): array
    {
        return [
            'no path' => [
                '',
                'http://example.org?foo=baz#boo',
            ],
            'empty path' => [
                '/',
                'http://example.org/?foo=baz#boo',
            ],
            'path only' => [
                '/hello/world',
                'http://example.org/hello/world?foo=baz#boo',
            ],
            'path and host' => [
                'http://another-example.org/hello/world',
                'http://example.org/hello/world?foo=baz#boo',
            ],
            'path and query' => [
                '/hello/world?another=foo',
                'http://example.org/hello/world?another=foo#boo',
            ],
            'path and fragment' => [
                '/hello/world#whats-going-on',
                'http://example.org/hello/world?foo=baz#whats-going-on',
            ],
            'all components' => [
                '/hello/world?another=foo#whats-going-on',
                'http://example.org/hello/world?another=foo#whats-going-on',
            ],
        ];
    }
}
