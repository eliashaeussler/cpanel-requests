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
use EliasHaeussler\CpanelRequests\Http\Response\WebResponse;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * Class WebResponseTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class WebResponseTest extends TestCase
{
    /**
     * @test
     * @throws InvalidResponseDataException
     */
    public function isValidReturnsTrueIfResponseWasSuccessful(): void
    {
        $response = new Response();
        $subject = new WebResponse($response);

        static::assertTrue($subject->isValid());

        $response = new Response(404);
        $subject = new WebResponse($response);

        static::assertFalse($subject->isValid());
    }

    /**
     * @test
     * @throws InvalidResponseDataException
     */
    public function getDataReturnsResponseBody(): void
    {
        $response = new Response(200, [], 'hello world!');
        $subject = new WebResponse($response);

        static::assertSame('hello world!', $subject->getData());
    }

    /**
     * @test
     * @throws InvalidResponseDataException
     */
    public function getResponseReturnsResponseObject(): void
    {
        $response = new Response();
        $subject = new WebResponse($response);

        static::assertSame($response, $subject->getResponse());
    }
}
