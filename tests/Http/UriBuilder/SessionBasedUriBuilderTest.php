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

namespace EliasHaeussler\CpanelRequests\Tests\Http\UriBuilder;

use EliasHaeussler\CpanelRequests\Application;
use EliasHaeussler\CpanelRequests\Exception;
use EliasHaeussler\CpanelRequests\Http;
use EliasHaeussler\CpanelRequests\Tests;
use Generator;
use Psr\Http\Message;

/**
 * SessionBasedUriBuilderTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class SessionBasedUriBuilderTest extends Tests\MockServerAwareTestCase
{
    private Application\Session\WebSession $session;
    private Http\UriBuilder\SessionBasedUriBuilder $subject;

    protected function setUp(): void
    {
        $this->session = new Application\Session\WebSession(self::getMockServerBaseUri());
        $this->subject = new Http\UriBuilder\SessionBasedUriBuilder($this->session);
    }

    /**
     * @test
     */
    public function buildUriForRequestThrowsExceptionIfSessionIsNotActive(): void
    {
        $request = new Http\Request\ApiRequest(self::getMockServerBaseUri(), 'foo');

        $this->expectException(Exception\SessionException::class);
        $this->expectExceptionCode(1656758037);
        $this->expectExceptionMessage('No active session found.');

        $this->subject->buildUriForRequest($request);
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
        self::createMockResponse(['status' => 1, 'security_token' => '123'], requestPath: '/login');
        self::createMockResponse(['status' => 1, 'data' => ['foo' => 'bar']]);

        $this->session->start('foo', 'bar');

        $request = new Http\Request\ApiRequest($baseUri, 'foo', 'bar', ['hello' => 'world']);

        $actual = $this->subject->buildUriForRequest($request);

        self::assertSame($expected, (string) $actual);
    }

    /**
     * @return Generator<string, array{Message\UriInterface, string}>
     */
    public function buildUriForRequestAppliesModuleAndFunctionToBaseUriDataProvider(): Generator
    {
        self::startMockServer();

        $baseUri = self::getMockServerBaseUri();
        $expectedUri = $baseUri
            ->withPath('/123/execute/foo/bar')
            ->withQuery('hello=world')
        ;

        yield 'protocol and host only' => [
            $baseUri,
            (string) $expectedUri,
        ];
        yield 'with additional base path' => [
            $baseUri->withPath('/dummy'),
            (string) $expectedUri->withPath('/dummy/123/execute/foo/bar'),
        ];
        yield 'with additional query params' => [
            $baseUri->withQuery('foo=baz'),
            (string) $expectedUri->withQuery('foo=baz&hello=world'),
        ];
        yield 'with additional fragment' => [
            $baseUri->withFragment('boo'),
            (string) $expectedUri->withFragment('boo'),
        ];
    }
}
