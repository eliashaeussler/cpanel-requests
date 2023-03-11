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

namespace EliasHaeussler\CpanelRequests\Tests\Application\Authorization;

use donatj\MockWebServer;
use EliasHaeussler\CpanelRequests\Application;
use EliasHaeussler\CpanelRequests\Http;
use EliasHaeussler\CpanelRequests\Tests;
use OTPHP\TOTP;
use ParagonIE\ConstantTime;
use PHPUnit\Framework;

/**
 * HttpAuthorizationTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class HttpAuthorizationTest extends Tests\MockServerAwareTestCase
{
    private Application\Authorization\HttpAuthorization $subject;
    private Http\Response\ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->subject = new Application\Authorization\HttpAuthorization('foo', 'bar');
        $this->responseFactory = new Http\Response\ResponseFactory();
    }

    #[Framework\Attributes\Test]
    public function sendAuthorizedRequestCreatesANewSessionIfSessionIsNotActive(): void
    {
        self::restartMockServer();
        self::createMockResponse(['status' => 1, 'security_token' => '123']);

        self::assertSame(0, self::getNumberOfMockServerRequests());

        $request = new Http\Request\ApiRequest(self::getMockServerBaseUri(), 'foo');

        $this->subject->sendAuthorizedRequest('GET', $request);

        self::assertSame(2, self::getNumberOfMockServerRequests());

        $this->subject->sendAuthorizedRequest('GET', $request);

        self::assertSame(3, self::getNumberOfMockServerRequests());
    }

    #[Framework\Attributes\Test]
    public function sendAuthorizedRequestAuthorizesAndSendsGivenRequest(): void
    {
        $request = new Http\Request\ApiRequest(self::getMockServerBaseUri(), 'foo');

        self::createMockResponse(['status' => 1, 'security_token' => '123'], requestPath: '/login');
        self::createMockResponse(['status' => 1], requestPath: '/123/execute/foo');

        $actual = $this->subject->sendAuthorizedRequest('GET', $request);
        $response = $this->responseFactory->createFromResponse($actual);

        self::assertEquals((object) ['status' => 1], $response->getData());
    }

    #[Framework\Attributes\Test]
    public function sendAuthorizedRequestProvidesOTPTokenIfOTPSecretIsGiven(): void
    {
        self::restartMockServer();
        self::createMockResponse(['status' => 1, 'security_token' => '123']);

        $otpSecret = ConstantTime\Base32::encode(bin2hex(random_bytes(32)));

        self::assertNotEmpty($otpSecret);

        $otp = TOTP::createFromSecret($otpSecret)->now();
        $subject = new Application\Authorization\HttpAuthorization('foo', 'bar', $otpSecret);
        $request = new Http\Request\ApiRequest(self::getMockServerBaseUri(), 'foo');

        $subject->sendAuthorizedRequest('GET', $request);

        $loginRequest = self::getMockServer()->getRequestByOffset(0);

        self::assertInstanceOf(MockWebServer\RequestInfo::class, $loginRequest);
        self::assertIsArray($loginRequest->getParsedUri());
        self::assertSame('login_only=1&user=foo&pass=bar&tfa_token='.$otp, $loginRequest->getParsedUri()['query']);
    }
}
