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

namespace EliasHaeussler\CpanelRequests\Tests\Application;

use EliasHaeussler\CpanelRequests\Application;
use EliasHaeussler\CpanelRequests\Exception;
use EliasHaeussler\CpanelRequests\Http;
use EliasHaeussler\CpanelRequests\Tests;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework;

/**
 * CPanelTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CPanelTest extends Tests\MockServerAwareTestCase
{
    private Tests\Fixtures\DummyAuthorization $authorization;
    private Application\CPanel $subject;

    protected function setUp(): void
    {
        $this->authorization = new Tests\Fixtures\DummyAuthorization();
        $this->subject = new Application\CPanel(
            $this->authorization,
            self::getMockServer()->getHost(),
            self::getMockServer()->getPort(),
            Http\Protocol::Http
        );
    }

    #[Framework\Attributes\Test]
    public function apiThrowsExceptionIfRequestFails(): void
    {
        $this->authorization->expectedException = Exception\InvalidResponseDataException::create();

        $this->expectException(Exception\RequestFailedException::class);
        $this->expectExceptionCode(1589836385);
        $this->expectExceptionMessage('Error during API request: Request failed. Please check the request URL and try again.');

        $this->subject->api('foo', 'bar');
    }

    #[Framework\Attributes\Test]
    public function apiReturnsApiResponse(): void
    {
        $response = (new Response())
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Utils::streamFor('{"foo":"bar"}'))
        ;

        $this->authorization->expectedResponse = $response;

        $actual = $this->subject->api('foo', 'bar');

        self::assertSame($response, $actual->getOriginalResponse());
        self::assertEquals((object) ['foo' => 'bar'], $actual->getData());
    }
}
