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
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * Class JsonResponseTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class JsonResponseTest extends TestCase
{
    /**
     * @test
     * @throws InvalidResponseDataException
     */
    public function constructorParsesResponseDataCorrectly(): void
    {
        $response = new Response(200, [], '{"foo":"baz"}');
        $expected = new \stdClass();
        $expected->foo = 'baz';

        $subject = new JsonResponse($response);

        static::assertEquals($expected, $subject->getData());
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionIfResponseContainsInvalidJson(): void
    {
        $response = new Response(200, [], 'foo');

        $this->expectException(InvalidResponseDataException::class);
        $this->expectExceptionCode(1544739719);

        new JsonResponse($response);
    }

    /**
     * @test
     * @throws InvalidResponseDataException
     */
    public function isValidReturnsValidityStateOfResponseDataCorrectly(): void
    {
        $response = new Response(200, [], '{"status":1,"data":{"hello":"world"}}');
        $subject = new JsonResponse($response);

        static::assertTrue($subject->isValid());
        static::assertFalse($subject->isValid('foo'));
    }

    /**
     * @test
     * @throws InvalidResponseDataException
     */
    public function getResponseReturnsResponseObject(): void
    {
        $response = new Response(200, [], '{}');
        $subject = new JsonResponse($response);

        static::assertSame($response, $subject->getResponse());
    }
}
