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

namespace EliasHaeussler\CpanelRequests\Application\Authorization;

use EliasHaeussler\CpanelRequests\Application;
use EliasHaeussler\CpanelRequests\Exception;
use EliasHaeussler\CpanelRequests\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie;
use GuzzleHttp\Psr7;
use GuzzleHttp\RequestOptions;
use OTPHP\TOTP;
use Psr\Http\Client as PsrClient;
use Psr\Http\Message;

use function assert;
use function is_string;
use function register_shutdown_function;

/**
 * HttpAuthorization.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class HttpAuthorization implements AuthorizationInterface
{
    private readonly Client $client;
    private readonly Message\RequestFactoryInterface $requestFactory;
    private ?Http\UriBuilder\UriBuilderInterface $uriBuilder = null;
    private ?Application\Session\WebSession $session = null;

    public function __construct(
        private readonly string $username,
        private readonly string $password,
        private readonly ?string $otpSecret = null,
    ) {
        $this->client = new Client();
        $this->requestFactory = new Psr7\HttpFactory();
    }

    public function sendAuthorizedRequest(
        string $method,
        Http\Request\ApiRequest $request,
        array $options = []
    ): Message\ResponseInterface {
        // Assure active session exists
        $this->login($request->getBaseUri());
        assert($this->session instanceof Application\Session\WebSession);

        // Add cookie file
        $cookieJar = new Cookie\FileCookieJar($this->session->getCookie()->getPathname(), true);
        $options[RequestOptions::COOKIES] = $cookieJar;

        // Create URI
        $uriBuilder = $this->createUriBuilder();
        $uri = $uriBuilder->buildUriForRequest($request);

        return $this->client->send($this->requestFactory->createRequest($method, $uri), $options);
    }

    /**
     * @throws PsrClient\ClientExceptionInterface
     */
    private function login(Message\UriInterface $baseUri): void
    {
        if ($this->hasActiveSession()) {
            return;
        }

        $this->session = new Application\Session\WebSession($baseUri);
        $this->session->start($this->username, $this->password, $this->getOTP());

        // Perform logout on shutdown
        register_shutdown_function($this->logout(...));
    }

    /**
     * @throws PsrClient\ClientExceptionInterface
     *
     * @codeCoverageIgnore
     */
    private function logout(): void
    {
        if (!$this->hasActiveSession()) {
            return;
        }

        $this->session?->stop();
    }

    private function hasActiveSession(): bool
    {
        return true === $this->session?->isActive();
    }

    private function getOTP(): ?string
    {
        if (!is_string($this->otpSecret) || '' === trim($this->otpSecret)) {
            return null;
        }

        return TOTP::create(trim($this->otpSecret))->now();
    }

    private function createUriBuilder(): Http\UriBuilder\UriBuilderInterface
    {
        // @codeCoverageIgnoreStart
        if (null === $this->session) {
            throw Exception\SessionException::forInactiveSession();
        }
        // @codeCoverageIgnoreEnd

        if (null === $this->uriBuilder) {
            $this->uriBuilder = new Http\UriBuilder\SessionBasedUriBuilder($this->session);
        }

        return $this->uriBuilder;
    }
}
