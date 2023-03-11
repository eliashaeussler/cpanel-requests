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

namespace EliasHaeussler\CpanelRequests\Tests\Http\Response;

use EliasHaeussler\CpanelRequests\Http;
use Generator;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use stdClass;

/**
 * ResponseFactoryTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ResponseFactoryTest extends Framework\TestCase
{
    private Http\Response\ResponseFactory $subject;

    protected function setUp(): void
    {
        $this->subject = new Http\Response\ResponseFactory();
    }

    /**
     * @param class-string<Http\Response\ResponseInterface> $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('createReturnsResponseFromGivenTypeDataProvider')]
    public function createReturnsResponseFromGivenType(string $type, string $expected): void
    {
        $response = new Psr7\Response(body: '{}');

        self::assertInstanceOf($expected, $this->subject->create($type, $response));
    }

    #[Framework\Attributes\Test]
    public function createReturnsNullResponseIfRegisteredRepresentationsAreInvalid(): void
    {
        $response = new Psr7\Response();

        self::assertInstanceOf(Http\Response\NullResponse::class, $this->subject->create('foo', $response));
        self::assertInstanceOf(Http\Response\NullResponse::class, $this->subject->create('dummy', $response));
    }

    #[Framework\Attributes\Test]
    public function createFromResponseReturnsJsonResponse(): void
    {
        $response = new Psr7\Response(200, ['Content-Type' => 'application/json'], '{"foo":"baz"}');
        $expectedData = new stdClass();
        $expectedData->foo = 'baz';

        $result = $this->subject->createFromResponse($response);

        self::assertInstanceOf(Http\Response\JsonResponse::class, $result);
        self::assertEquals($expectedData, $result->getData());
    }

    #[Framework\Attributes\Test]
    public function createFromResponseReturnsWebResponse(): void
    {
        $response = new Psr7\Response(body: 'hello world!');
        $expectedData = 'hello world!';

        $result = $this->subject->createFromResponse($response);

        self::assertInstanceOf(Http\Response\WebResponse::class, $result);
        self::assertSame($expectedData, $result->getData());
    }

    /**
     * @return Generator<string, array{string, class-string<Http\Response\ResponseInterface>}>
     */
    public static function createReturnsResponseFromGivenTypeDataProvider(): Generator
    {
        yield 'web response' => ['web', Http\Response\WebResponse::class];
        yield 'json response' => ['json', Http\Response\JsonResponse::class];
        yield 'mistyped valid response' => ['   WEb   ', Http\Response\WebResponse::class];
        yield 'unsupported type' => ['foo', Http\Response\NullResponse::class];
    }
}
