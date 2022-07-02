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

namespace EliasHaeussler\CpanelRequests\Tests\Application;

use EliasHaeussler\CpanelRequests\Application;
use EliasHaeussler\CpanelRequests\Exception;
use EliasHaeussler\CpanelRequests\Http;
use EliasHaeussler\CpanelRequests\Tests;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Prophecy\Argument;
use Prophecy\PhpUnit;
use Prophecy\Prophecy;

/**
 * CPanelTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CPanelTest extends Tests\MockServerAwareTestCase
{
    use PhpUnit\ProphecyTrait;

    /**
     * @var Prophecy\ObjectProphecy<Application\Authorization\AuthorizationInterface>
     */
    private Prophecy\ObjectProphecy $authorizationProphecy;
    private Application\CPanel $subject;

    protected function setUp(): void
    {
        $this->authorizationProphecy = $this->prophesize(Application\Authorization\AuthorizationInterface::class);
        $this->subject = new Application\CPanel(
            $this->authorizationProphecy->reveal(),
            self::getMockServer()->getHost(),
            self::getMockServer()->getPort(),
            Http\Protocol::Http
        );
    }

    /**
     * @test
     */
    public function apiThrowsExceptionIfRequestFails(): void
    {
        /* @noinspection PhpParamsInspection */
        $this->authorizationProphecy->sendAuthorizedRequest(Argument::cetera())
            ->willThrow(Exception\InvalidResponseDataException::create())
            ->shouldBeCalledOnce()
        ;

        $this->expectException(Exception\RequestFailedException::class);
        $this->expectExceptionCode(1589836385);
        $this->expectExceptionMessage('Error during API request: Request failed. Please check the request URL and try again.');

        $this->subject->api('foo', 'bar');
    }

    /**
     * @test
     */
    public function apiReturnsApiResponse(): void
    {
        $response = (new Response())
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Utils::streamFor('{"foo":"bar"}'))
        ;

        /* @noinspection PhpParamsInspection */
        $this->authorizationProphecy->sendAuthorizedRequest(Argument::cetera())
            ->willReturn($response)
            ->shouldBeCalledOnce()
        ;

        $actual = $this->subject->api('foo', 'bar');

        self::assertSame($response, $actual->getOriginalResponse());
        self::assertEquals((object) ['foo' => 'bar'], $actual->getData());
    }
}
