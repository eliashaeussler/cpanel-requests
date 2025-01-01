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

namespace EliasHaeussler\CpanelRequests\Tests\Application\Session;

use EliasHaeussler\CpanelRequests\Application;
use EliasHaeussler\CpanelRequests\Exception;
use EliasHaeussler\CpanelRequests\Http;
use EliasHaeussler\CpanelRequests\Tests;
use Generator;
use PHPUnit\Framework;

use function sprintf;

/**
 * WebSessionTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class WebSessionTest extends Tests\MockServerAwareTestCase
{
    private Application\Session\WebSession $subject;

    protected function setUp(): void
    {
        $this->subject = new Application\Session\WebSession(self::getMockServerBaseUri());
    }

    #[Framework\Attributes\Test]
    public function startThrowsExceptionIfResponseIsNotAJsonResponse(): void
    {
        self::createMockResponse('Hello world!');

        $this->expectException(Exception\RequestFailedException::class);
        $this->expectExceptionCode(1592850467);
        $this->expectExceptionMessage(
            sprintf('Expected "%s", got "%s" instead.', Http\Response\JsonResponse::class, Http\Response\WebResponse::class),
        );

        $this->subject->start('foo', 'bar');
    }

    #[Framework\Attributes\Test]
    public function startThrowsExceptionIfExpectedSessionTokenParameterIsInvalid(): void
    {
        self::createMockResponse(['status' => 1]);

        $this->expectException(Exception\AuthenticationFailedException::class);
        $this->expectExceptionCode(1544739118);
        $this->expectExceptionMessage('Authentication failed. Please check your login credentials and try again.');

        $this->subject->start('foo', 'bar');
    }

    #[Framework\Attributes\Test]
    public function startThrowsExceptionIfExpectedSessionTokenParameterIsNull(): void
    {
        self::createMockResponse(['status' => 1, 'security_token' => null]);

        $this->expectException(Exception\SessionException::class);
        $this->expectExceptionCode(1656758037);
        $this->expectExceptionMessage('No active session found.');

        $this->subject->start('foo', 'bar');
    }

    #[Framework\Attributes\Test]
    public function startThrowsExceptionIfExpectedSessionTokenParameterIsEmpty(): void
    {
        self::createMockResponse(['status' => 1, 'security_token' => '']);

        $this->expectException(Exception\SessionException::class);
        $this->expectExceptionCode(1656758643);
        $this->expectExceptionMessage('Session identifier is invalid or empty.');

        $this->subject->start('foo', 'bar');
    }

    /**
     * @param array{path: string, query: string} $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('startStartsANewSessionWithGivenCredentialsDataProvider')]
    public function startStartsANewSessionWithGivenCredentials(?string $otp, array $expected): void
    {
        self::createMockResponse(['status' => 1, 'security_token' => '123']);

        $this->subject->start('foo', 'bar', $otp);

        self::assertTrue($this->subject->isActive());
        self::assertSame('123', $this->subject->getIdentifier());

        $lastRequest = self::getLastMockServerRequest();

        self::assertSame($expected, $lastRequest->getParsedUri());
    }

    #[Framework\Attributes\Test]
    public function stopDoesNothingIfSessionIsNotActive(): void
    {
        self::restartMockServer();

        $this->subject->stop();

        self::assertNull(self::getMockServer()->getLastRequest());
    }

    #[Framework\Attributes\Test]
    public function stopClosesSessionOnSuccessfulLogout(): void
    {
        self::createMockResponse(['status' => 1, 'security_token' => '123']);

        $this->subject->start('foo', 'bar');

        self::assertTrue($this->subject->isActive());

        self::createMockResponse('Success!');

        self::assertTrue($this->subject->stop());
        self::assertFalse($this->subject->isActive());
    }

    #[Framework\Attributes\Test]
    public function isActiveReturnsFalseOnInitialState(): void
    {
        self::assertFalse($this->subject->isActive());
    }

    #[Framework\Attributes\Test]
    public function getIdentifierReturnsNullOnInitialState(): void
    {
        self::assertNull($this->subject->getIdentifier());
    }

    /**
     * @return Generator<string, array{string|null, array{path: string, query: string}}>
     */
    public static function startStartsANewSessionWithGivenCredentialsDataProvider(): Generator
    {
        yield 'without OTP' => [
            null,
            ['path' => '/login', 'query' => 'login_only=1&user=foo&pass=bar'],
        ];
        yield 'with OTP' => [
            '123456',
            ['path' => '/login', 'query' => 'login_only=1&user=foo&pass=bar&tfa_token=123456'],
        ];
    }
}
