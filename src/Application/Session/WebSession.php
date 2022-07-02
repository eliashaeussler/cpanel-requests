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

namespace EliasHaeussler\CpanelRequests\Application\Session;

use EliasHaeussler\CpanelRequests\Exception;
use EliasHaeussler\CpanelRequests\Http;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7;
use Psr\Http\Client;
use Psr\Http\Message;

/**
 * Representation of a web session of a single application.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class WebSession
{
    private const SESSION_TOKEN_PARAMETER = 'security_token';

    private bool $active;
    private Http\UriBuilder\SessionBasedUriBuilder $uriBuilder;
    private Message\RequestFactoryInterface $requestFactory;
    private Http\Response\ResponseFactory $responseFactory;
    private GuzzleClient $client;
    private ?string $identifier = null;

    public function __construct(
        private readonly Message\UriInterface $baseUri,
    ) {
        $this->active = false;
        $this->uriBuilder = new Http\UriBuilder\SessionBasedUriBuilder($this);
        $this->requestFactory = new Psr7\HttpFactory();
        $this->responseFactory = new Http\Response\ResponseFactory();
        $this->client = new GuzzleClient();
    }

    /**
     * @throws Client\ClientExceptionInterface
     * @throws Exception\AuthenticationFailedException
     * @throws Exception\InvalidResponseDataException
     * @throws Exception\RequestFailedException
     */
    public function start(string $username, string $password, ?string $otp = null): void
    {
        // Build request object
        $request = new Http\Request\ApiRequest($this->baseUri, 'login');
        $request->setParameters([
            'login_only' => 1,
            'user' => $username,
            'pass' => $password,
        ]);

        if (null !== $otp) {
            $request->addParameter('tfa_token', $otp);
        }

        // Send authentication request
        $uriBuilder = new Http\UriBuilder\DefaultUriBuilder();
        $uri = $uriBuilder->buildUriForRequest($request);
        $response = $this->sendRequest('GET', $uri);

        // Throw exception if response is no JSON response
        if (!($response instanceof Http\Response\JsonResponse)) {
            throw Exception\RequestFailedException::forUnexpectedResponse(Http\Response\JsonResponse::class, $response);
        }

        // Throw exception if API response is not valid
        if (!$response->isValid(self::SESSION_TOKEN_PARAMETER)) {
            throw Exception\AuthenticationFailedException::create();
        }

        $this->active = true;
        $this->identifier = $response->getData()->{self::SESSION_TOKEN_PARAMETER};

        $this->validateIdentifier();
    }

    /**
     * @throws Exception\InvalidResponseDataException
     * @throws Client\ClientExceptionInterface
     */
    public function stop(): bool
    {
        if (!$this->active) {
            return true;
        }

        $request = new Http\Request\ApiRequest($this->baseUri, 'logout');
        $uri = $this->uriBuilder->buildUriForRequest($request);
        $response = $this->sendRequest('GET', $uri);

        if ($response->isValid()) {
            $this->active = false;
        }

        return !$this->active;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @throws Client\ClientExceptionInterface
     */
    private function sendRequest(string $method, Message\UriInterface $uri): Http\Response\ResponseInterface
    {
        $request = $this->requestFactory->createRequest($method, $uri);
        $response = $this->client->sendRequest($request);

        return $this->responseFactory->createFromResponse($response);
    }

    private function validateIdentifier(): void
    {
        if (null === $this->identifier) {
            throw Exception\SessionException::forInactiveSession();
        }
        if ('' === trim($this->identifier)) {
            throw Exception\SessionException::forInvalidSessionIdentifier();
        }
    }
}
