<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\Tests\Unit\Application;

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

use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use EliasHaeussler\CpanelRequests\Application\CPanel;
use EliasHaeussler\CpanelRequests\Application\Session\WebSession;
use EliasHaeussler\CpanelRequests\Exception\AuthenticationFailedException;
use EliasHaeussler\CpanelRequests\Exception\InactiveSessionException;
use EliasHaeussler\CpanelRequests\Exception\NotAuthenticatedException;
use EliasHaeussler\CpanelRequests\Exception\RequestFailedException;
use EliasHaeussler\CpanelRequests\Http\Protocol;
use EliasHaeussler\CpanelRequests\Http\Response\JsonResponse;
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;
use PHPUnit\Framework\TestCase;

/**
 * Class CPanelTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class CPanelTest extends TestCase
{
    protected const MOCK_SERVER_HOST = '127.0.0.1';
    protected const MOCK_SERVER_PORT = 2083;
    protected const SESSION_IDENTIFIER = 'foo';
    protected const APP_USERNAME = 'foo';
    protected const APP_PASSWORD = 'baz';

    /**
     * @var MockWebServer
     */
    protected static $server;

    /**
     * @var string
     */
    protected $totpSecret;

    /**
     * @var CPanel
     */
    protected $subject;

    public static function setUpBeforeClass(): void
    {
        static::$server = new MockWebServer(static::MOCK_SERVER_PORT, static::MOCK_SERVER_HOST);
        static::$server->start();
        static::assertTrue(static::$server->isRunning());
    }

    protected function setUp(): void
    {
        $this->totpSecret = Base32::encode(bin2hex(random_bytes(32)));
        $this->subject = new CPanel(
            static::MOCK_SERVER_HOST,
            self::APP_USERNAME,
            self::APP_PASSWORD,
            static::MOCK_SERVER_PORT,
            Protocol::HTTP,
            $this->totpSecret
        );
    }

    /**
     * @test
     */
    public function canCreateCPanel(): void
    {
        static::assertInstanceOf(CPanel::class, $this->subject);
        static::assertFalse($this->subject->hasActiveSession());
    }

    /**
     * @test
     * @throws AuthenticationFailedException
     * @throws InactiveSessionException
     * @throws RequestFailedException
     */
    public function authorizeCreatesValidSessionIfLoginIsSuccessful(): void
    {
        $this->mockSuccessfulAuthorization();

        $this->subject->authorize();

        static::assertLastRequestUri($this->buildLoginUri());
        static::assertTrue($this->subject->hasActiveSession());
        static::assertSame(static::SESSION_IDENTIFIER, $this->subject->getSession()->getIdentifier());
    }

    /**
     * @test
     * @throws AuthenticationFailedException
     * @throws InactiveSessionException
     * @throws RequestFailedException
     */
    public function authorizeThrowsExceptionIfLoginIsNotSuccessful(): void
    {
        $this->mockFailedAuthorization();

        $this->expectException(AuthenticationFailedException::class);
        $this->expectExceptionCode(1544739118);

        $this->subject->authorize();

        static::assertLastRequestUri($this->buildLoginUri());
    }

    /**
     * @test
     * @throws NotAuthenticatedException
     * @throws RequestFailedException
     */
    public function apiThrowsExceptionIfApplicationDoesNotHaveActiveSession(): void
    {
        $this->expectException(NotAuthenticatedException::class);
        $this->expectExceptionCode(1544738659);

        $this->subject->api('module', 'function');
    }

    /**
     * @test
     * @throws AuthenticationFailedException
     * @throws InactiveSessionException
     * @throws NotAuthenticatedException
     * @throws RequestFailedException
     */
    public function apiReturnsSuccessfulApiResponse(): void
    {
        $this->mockSuccessfulAuthorization();
        $this->mockRequest(sprintf('/%s/execute/module/function', static::SESSION_IDENTIFIER), ['hello' => 'world']);

        $result = $this->subject->authorize()->api('module', 'function', ['foo' => 'baz']);

        static::assertLastRequestUri(sprintf('/%s/execute/module/function?foo=baz', static::SESSION_IDENTIFIER));
        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertObjectHasAttribute('hello', $result->getData());
        static::assertSame('world', $result->getData()->hello);
    }

    /**
     * @test
     * @throws AuthenticationFailedException
     * @throws InactiveSessionException
     * @throws NotAuthenticatedException
     * @throws RequestFailedException
     */
    public function apiThrowsExceptionIfApiResponseContainsInvalidJSON(): void
    {
        $this->mockSuccessfulAuthorization();
        $this->mockRequest(sprintf('/%s/execute/module/function', static::SESSION_IDENTIFIER), 'foo', false);

        $this->expectException(RequestFailedException::class);
        $this->expectExceptionCode(1589836385);

        $this->subject->authorize()->api('module', 'function', ['foo' => 'baz']);

        static::assertLastRequestUri(sprintf('/%s/execute/module/function?foo=baz', static::SESSION_IDENTIFIER));
    }

    /**
     * @test
     */
    public function getSessionReturnsNullIfAuthorizationHasNotBeenProcessed(): void
    {
        static::assertNull($this->subject->getSession());
    }

    /**
     * @test
     * @throws AuthenticationFailedException
     * @throws InactiveSessionException
     * @throws RequestFailedException
     */
    public function getSessionReturnsCurrentSessionIfAuthorizationWasSuccessful(): void
    {
        $this->mockSuccessfulAuthorization();

        $session = $this->subject->authorize()->getSession();

        static::assertInstanceOf(WebSession::class, $session);
        static::assertSame(static::SESSION_IDENTIFIER, $session->getIdentifier());
    }

    /**
     * @test
     * @throws AuthenticationFailedException
     * @throws InactiveSessionException
     * @throws RequestFailedException
     */
    public function hasActiveSessionReturnsStateOfAvailabilityOfApplicationSession(): void
    {
        static::assertFalse($this->subject->hasActiveSession());

        $this->mockSuccessfulAuthorization();

        static::assertTrue($this->subject->authorize()->hasActiveSession());
    }

    /**
     * @test
     * @throws RequestFailedException
     */
    public function logoutDoesNotTriggerLogoutRequestIfNoSessionIsCurrentlyActive(): void
    {
        $this->subject->logout();

        static::assertNotLastRequestUri('/logout');
    }

    /**
     * @test
     * @throws AuthenticationFailedException
     * @throws InactiveSessionException
     * @throws RequestFailedException
     */
    public function logoutFailsIfApiResponseIfNotValid(): void
    {
        $this->mockSuccessfulAuthorization();
        $this->mockRequest('/logout', 'logout failed', false, 401);

        $this->expectException(RequestFailedException::class);
        $this->expectExceptionCode(1589836385);

        $this->subject->authorize()->logout();

        static::assertLastRequestUri('/logout');
    }

    /**
     * @test
     * @throws AuthenticationFailedException
     * @throws InactiveSessionException
     * @throws RequestFailedException
     */
    public function logoutTriggersLogoutRequestOnActiveSession(): void
    {
        $this->mockSuccessfulAuthorization();
        $this->mockRequest('/logout', 'bye', false);

        $this->subject->authorize()->logout();

        static::assertLastRequestUri('/logout');
        static::assertFalse($this->subject->getSession()->isActive());
    }

    // @todo add tests for request() method

    protected function buildLoginUri(): string
    {
        return sprintf(
            '/login?login_only=1&user=%s&pass=%s&tfa_token=%s',
            static::APP_USERNAME,
            static::APP_PASSWORD,
            TOTP::create($this->totpSecret)->now()
        );
    }

    protected function mockSuccessfulAuthorization(): void
    {
        $this->mockRequest('/login', ['status' => 1, CPanel::SESSION_TOKEN_PARAMETER => static::SESSION_IDENTIFIER]);
    }

    protected function mockFailedAuthorization(): void
    {
        $this->mockRequest('/login', ['status' => 0]);
    }

    protected function mockRequest(string $path, $expectedResponse, bool $isJson = true, int $statusCode = 200): void
    {
        $path = '/' . ltrim($path, '/');
        if ($isJson) {
            $expectedResponse = json_encode($expectedResponse);
        }
        static::$server->setResponseOfPath($path, new Response($expectedResponse, [], $statusCode));
    }

    protected static function assertLastRequestUri(string $expected): void
    {
        static::assertSame($expected, static::$server->getLastRequest()->getRequestUri());
    }

    protected static function assertNotLastRequestUri(string $expected): void
    {
        $lastRequest = static::$server->getLastRequest();
        if ($lastRequest !== null) {
            static::assertNotSame($expected, $lastRequest->getRequestUri());
        } else {
            static::assertNull($lastRequest);
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (static::$server->isRunning()) {
            static::$server->stop();
        }
    }
}
