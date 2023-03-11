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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CpanelRequests\Tests\Http\Response;

use EliasHaeussler\CpanelRequests\Exception;
use EliasHaeussler\CpanelRequests\Http;
use Generator;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use stdClass;

/**
 * JsonResponseTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class JsonResponseTest extends Framework\TestCase
{
    /**
     * @var string
     */
    private const MIME_TYPE = 'application/json';

    #[Framework\Attributes\Test]
    public function constructorParsesResponseDataCorrectly(): void
    {
        $response = new Psr7\Response(body: '{"foo":"baz"}');
        $expected = new stdClass();
        $expected->foo = 'baz';

        $subject = new Http\Response\JsonResponse($response);

        self::assertEquals($expected, $subject->getData());
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfResponseContainsInvalidJson(): void
    {
        $response = new Psr7\Response(body: 'null');

        $this->expectException(Exception\InvalidResponseDataException::class);
        $this->expectExceptionCode(1544739719);
        $this->expectExceptionMessage('Request failed. Please check the request URL and try again.');

        new Http\Response\JsonResponse($response);
    }

    /**
     * @param array{Accept?: string, Content-Type?: string} $headers
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('supportsReturnsTrueIfResponseHeadersContainSupportedMimeTypeDataProvider')]
    public function supportsReturnsTrueIfResponseHeadersContainSupportedMimeType(array $headers, bool $expected): void
    {
        $response = new Psr7\Response(headers: $headers);

        self::assertSame($expected, Http\Response\JsonResponse::supports($response));
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('supportsReturnsTrueIfResponseBodyCanBeJsonDecodedDataProvider')]
    public function supportsReturnsTrueIfResponseBodyCanBeJsonDecoded(string $body, bool $expected): void
    {
        $response = new Psr7\Response(body: $body);

        self::assertSame($expected, Http\Response\JsonResponse::supports($response));
    }

    #[Framework\Attributes\Test]
    public function isValidReturnsValidityStateOfResponseDataCorrectly(): void
    {
        $response = new Psr7\Response(body: '{"status":1,"data":{"hello":"world"}}');
        $subject = new Http\Response\JsonResponse($response);

        self::assertTrue($subject->isValid());
        self::assertFalse($subject->isValid('foo'));
    }

    #[Framework\Attributes\Test]
    public function getResponseReturnsResponseObject(): void
    {
        $response = new Psr7\Response(body: '{}');
        $subject = new Http\Response\JsonResponse($response);

        self::assertSame($response, $subject->getOriginalResponse());
    }

    /**
     * @return Generator<string, array{array{Accept?: string, Content-Type?: string}, bool}>
     */
    public static function supportsReturnsTrueIfResponseHeadersContainSupportedMimeTypeDataProvider(): Generator
    {
        yield 'no supported headers' => [[], false];
        yield '"Accept" header' => [['Accept' => self::MIME_TYPE], true];
        yield '"Content-Type" header' => [['Content-Type' => self::MIME_TYPE], true];
        yield '"Accept" and "Content-Type" header' => [['Accept' => self::MIME_TYPE, 'Content-Type' => self::MIME_TYPE], true];
        yield 'supported header with additional metadata' => [['Content-Type' => self::MIME_TYPE.'; charset=utf-8'], true];
    }

    /**
     * @return Generator<string, array{string, bool}>
     */
    public static function supportsReturnsTrueIfResponseBodyCanBeJsonDecodedDataProvider(): Generator
    {
        yield 'no JSON' => ['foo', false];
        yield 'unsupported JSON' => ['"foo"', false];
        yield 'supported JSON' => ['{"foo":"bar"}', true];
    }
}
