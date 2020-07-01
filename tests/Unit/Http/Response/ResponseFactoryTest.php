<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\Tests\Unit\Http\Response;

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

use EliasHaeussler\CpanelRequests\Exception\InvalidResponseDataException;
use EliasHaeussler\CpanelRequests\Http\Response\JsonResponse;
use EliasHaeussler\CpanelRequests\Http\Response\NullResponse;
use EliasHaeussler\CpanelRequests\Http\Response\ResponseFactory;
use EliasHaeussler\CpanelRequests\Http\Response\WebResponse;
use EliasHaeussler\CpanelRequests\Tests\Unit\Fixtures\Http\Response\DummyResponseFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * Class ResponseFactoryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class ResponseFactoryTest extends TestCase
{
    /**
     * @test
     * @dataProvider createReturnsResponseFromGivenTypeDataProvider
     * @param string|null $type
     * @param string $expectedClass
     * @throws InvalidResponseDataException
     */
    public function createReturnsResponseFromGivenType(?string $type, string $expectedClass): void
    {
        $response = new Response(200, [], '{}');
        static::assertInstanceOf($expectedClass, ResponseFactory::create($type, $response));
    }

    /**
     * @test
     * @throws InvalidResponseDataException
     */
    public function createReturnsNullResponseIfRegisteredRepresentationsAreInvalid(): void
    {
        $response = new Response();

        static::assertInstanceOf(NullResponse::class, DummyResponseFactory::create('foo', $response));
        static::assertInstanceOf(NullResponse::class, DummyResponseFactory::create('dummy', $response));
    }

    /**
     * @test
     * @throws InvalidResponseDataException
     */
    public function createFromResponseReturnsJsonResponse(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], '{"foo":"baz"}');
        $expectedData = new \stdClass();
        $expectedData->foo = 'baz';

        $result = ResponseFactory::createFromResponse($response);

        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertEquals($expectedData, $result->getData());
    }

    /**
     * @test
     * @throws InvalidResponseDataException
     */
    public function createFromResponseReturnsWebResponse(): void
    {
        $response = new Response(200, [], 'hello world!');
        $expectedData = 'hello world!';

        $result = ResponseFactory::createFromResponse($response);

        static::assertInstanceOf(WebResponse::class, $result);
        static::assertSame($expectedData, $result->getData());
    }

    public function createReturnsResponseFromGivenTypeDataProvider(): array
    {
        return [
            'null' => [
                null,
                WebResponse::class,
            ],
            'web response' => [
                'web',
                WebResponse::class,
            ],
            'json response' => [
                'json',
                JsonResponse::class,
            ],
            'mistyped valid response' => [
                '   WEb   ',
                WebResponse::class,
            ],
            'unsupported type' => [
                'foo',
                NullResponse::class,
            ],
        ];
    }
}
