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
     * @test
     */
    public function constructorParsesResponseDataCorrectly(): void
    {
        $response = new Psr7\Response(body: '{"foo":"baz"}');
        $expected = new stdClass();
        $expected->foo = 'baz';

        $subject = new Http\Response\JsonResponse($response);

        self::assertEquals($expected, $subject->getData());
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionIfResponseContainsInvalidJson(): void
    {
        $response = new Psr7\Response(body: 'null');

        $this->expectException(Exception\InvalidResponseDataException::class);
        $this->expectExceptionCode(1544739719);
        $this->expectExceptionMessage('Request failed. Please check the request URL and try again.');

        new Http\Response\JsonResponse($response);
    }

    /**
     * @test
     * @dataProvider supportsReturnsTrueIfGivenResponseCanBeHandledDataProvider
     *
     * @param array{Accept?: string, Content-Type?: string} $headers
     */
    public function supportsReturnsTrueIfGivenResponseCanBeHandled(array $headers, bool $expected): void
    {
        $response = new Psr7\Response(headers: $headers);

        self::assertSame($expected, Http\Response\JsonResponse::supports($response));
    }

    /**
     * @test
     */
    public function isValidReturnsValidityStateOfResponseDataCorrectly(): void
    {
        $response = new Psr7\Response(body: '{"status":1,"data":{"hello":"world"}}');
        $subject = new Http\Response\JsonResponse($response);

        self::assertTrue($subject->isValid());
        self::assertFalse($subject->isValid('foo'));
    }

    /**
     * @test
     */
    public function getResponseReturnsResponseObject(): void
    {
        $response = new Psr7\Response(body: '{}');
        $subject = new Http\Response\JsonResponse($response);

        self::assertSame($response, $subject->getOriginalResponse());
    }

    /**
     * @return Generator<string, array{array{Accept?: string, Content-Type?: string}, bool}>
     */
    public function supportsReturnsTrueIfGivenResponseCanBeHandledDataProvider(): Generator
    {
        $mimeType = 'application/json';

        yield 'no supported headers' => [[], false];
        yield '"Accept" header' => [['Accept' => $mimeType], true];
        yield '"Content-Type" header' => [['Content-Type' => $mimeType], true];
        yield '"Accept" and "Content-Type" header' => [['Accept' => $mimeType, 'Content-Type' => $mimeType], true];
        yield 'supported header with additional metadata' => [['Content-Type' => $mimeType.'; charset=utf-8'], true];
    }
}
