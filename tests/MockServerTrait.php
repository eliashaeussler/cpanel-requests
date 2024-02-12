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

namespace EliasHaeussler\CpanelRequests\Tests;

use donatj\MockWebServer;
use EliasHaeussler\CpanelRequests\Http;
use GuzzleHttp\Psr7;
use Psr\Http\Message;
use Symfony\Component\Filesystem;

use function file_get_contents;
use function getenv;
use function is_array;
use function json_encode;

/**
 * MockServerTrait.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
trait MockServerTrait
{
    private static ?MockWebServer\MockWebServer $mockServer = null;

    protected static function startMockServer(
        string $host = '127.0.0.1',
        int $port = 2083,
    ): MockWebServer\MockWebServer {
        self::$mockServer?->stop();
        self::$mockServer = new MockWebServer\MockWebServer($port, $host);
        self::$mockServer->start();

        self::assertTrue(self::$mockServer->isRunning());

        return self::$mockServer;
    }

    protected static function restartMockServer(): void
    {
        self::assertNotNull(self::$mockServer, 'Mock server is not running.');

        static::startMockServer(self::$mockServer->getHost(), self::$mockServer->getPort());
    }

    protected static function getMockServer(): MockWebServer\MockWebServer
    {
        if (null === self::$mockServer) {
            self::startMockServer();
        }

        self::assertNotNull(self::$mockServer);

        return self::$mockServer;
    }

    protected static function getMockServerBaseUri(): Message\UriInterface
    {
        self::assertNotNull(self::$mockServer);

        return (new Psr7\Uri())
            ->withScheme(Http\Protocol::Http->value)
            ->withHost(self::$mockServer->getHost())
            ->withPort(self::$mockServer->getPort())
        ;
    }

    protected static function getLastMockServerRequest(): MockWebServer\RequestInfo
    {
        self::assertNotNull(self::$mockServer);

        $lastRequest = self::$mockServer->getLastRequest();

        self::assertInstanceOf(MockWebServer\RequestInfo::class, $lastRequest);

        return $lastRequest;
    }

    protected static function getNumberOfMockServerRequests(): int
    {
        self::assertNotNull(self::$mockServer);

        $webServerPath = getenv(MockWebServer\MockWebServer::TMP_ENV);

        self::assertIsString($webServerPath);

        $requestCountFile = Filesystem\Path::join(
            $webServerPath,
            MockWebServer\MockWebServer::REQUEST_COUNT_FILE,
        );

        self::assertFileExists($requestCountFile);

        return (int) file_get_contents($requestCountFile);
    }

    /**
     * @param string|array<string, mixed> $body
     * @param array<string, mixed>        $headers
     */
    protected static function createMockResponse(
        string|array $body,
        array $headers = [],
        int $statusCode = 200,
        ?string $requestPath = null,
    ): MockWebServer\Response {
        if (is_array($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
            $headers['Content-Type'] = 'application/json';
        }

        $response = new MockWebServer\Response($body, $headers, $statusCode);

        self::assertNotNull(self::$mockServer);

        if (null !== $requestPath) {
            self::$mockServer->setResponseOfPath($requestPath, $response);
        } else {
            self::$mockServer->setDefaultResponse($response);
        }

        return $response;
    }
}
