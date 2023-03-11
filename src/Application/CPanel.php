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

namespace EliasHaeussler\CpanelRequests\Application;

use EliasHaeussler\CpanelRequests\Exception;
use EliasHaeussler\CpanelRequests\Http;
use EliasHaeussler\CpanelRequests\Resource;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7;
use Monolog\Handler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Http\Client;
use Psr\Http\Message;

/**
 * General cPanel class.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class CPanel
{
    private readonly Message\UriInterface $baseUri;
    private readonly Http\Response\ResponseFactory $responseFactory;
    private readonly Resource\File $logFile;

    public function __construct(
        private readonly Authorization\AuthorizationInterface $authorization,
        private readonly string $host,
        private readonly int $port = 2083,
        private readonly Http\Protocol $protocol = Http\Protocol::Https,
    ) {
        $this->baseUri = $this->buildBaseUri();
        $this->responseFactory = new Http\Response\ResponseFactory();
        $this->logFile = Resource\Log::create();
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @see https://api.docs.cpanel.net/cpanel/introduction
     */
    public function api(string $module, string $function, array $parameters = []): Http\Response\ResponseInterface
    {
        // Create logger
        $requestId = uniqid('request-');
        $logger = new Logger($requestId);
        $logger->pushHandler(new Handler\StreamHandler($this->logFile->getPathname(), Level::Warning));

        // Create handler stack
        $stack = HandlerStack::create();
        $stack->push(Middleware::log($logger, new MessageFormatter()));
        $options = [
            'handler' => $stack,
        ];

        try {
            $request = new Http\Request\ApiRequest($this->baseUri, $module, $function, $parameters);
            $response = $this->authorization->sendAuthorizedRequest('GET', $request, $options);

            return $this->responseFactory->createFromResponse($response);
        } catch (Client\ClientExceptionInterface|Exception\Exception $exception) {
            throw Exception\RequestFailedException::create($exception);
        }
    }

    private function buildBaseUri(): Message\UriInterface
    {
        return (new Psr7\Uri())
            ->withScheme($this->protocol->value)
            ->withHost($this->host)
            ->withPort($this->port)
        ;
    }
}
