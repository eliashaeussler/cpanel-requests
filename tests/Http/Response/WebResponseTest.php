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

namespace EliasHaeussler\CpanelRequests\Tests\Http\Response;

use EliasHaeussler\CpanelRequests\Http;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

/**
 * WebResponseTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class WebResponseTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function isValidReturnsTrueIfResponseWasSuccessful(): void
    {
        $response = new Psr7\Response();
        $subject = new Http\Response\WebResponse($response);

        self::assertTrue($subject->isValid());

        $response = new Psr7\Response(404);
        $subject = new Http\Response\WebResponse($response);

        self::assertFalse($subject->isValid());
    }

    #[Framework\Attributes\Test]
    public function getDataReturnsResponseBody(): void
    {
        $response = new Psr7\Response(body: 'hello world!');
        $subject = new Http\Response\WebResponse($response);

        self::assertSame('hello world!', $subject->getData());
    }

    #[Framework\Attributes\Test]
    public function getResponseReturnsResponseObject(): void
    {
        $response = new Psr7\Response();
        $subject = new Http\Response\WebResponse($response);

        self::assertSame($response, $subject->getOriginalResponse());
    }
}
