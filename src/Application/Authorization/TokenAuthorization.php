<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cpanel-requests".
 *
 * Copyright (C) 2020-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CpanelRequests\Application\Authorization;

use EliasHaeussler\CpanelRequests\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message;

use function sprintf;

/**
 * TokenAuthorization.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class TokenAuthorization implements AuthorizationInterface
{
    private readonly Message\RequestFactoryInterface $requestFactory;
    private readonly Http\UriBuilder\TokenBasedUriBuilder $uriBuilder;
    private readonly Client $client;

    public function __construct(
        private readonly string $username,
        private readonly string $token,
    ) {
        $this->requestFactory = new Psr7\HttpFactory();
        $this->uriBuilder = new Http\UriBuilder\TokenBasedUriBuilder();
        $this->client = $this->createClient();
    }

    public function sendAuthorizedRequest(
        string $method,
        Http\Request\ApiRequest $request,
        array $options = [],
    ): Message\ResponseInterface {
        $uri = $this->uriBuilder->buildUriForRequest($request);
        $request = $this->requestFactory->createRequest($method, $uri);

        return $this->client->send($request, $options);
    }

    private function createClient(): Client
    {
        return new Client([
            RequestOptions::HEADERS => [
                'Authorization' => sprintf('cpanel %s:%s', $this->username, $this->token),
            ],
        ]);
    }
}
