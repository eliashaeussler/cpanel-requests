<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\Application;

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

use EliasHaeussler\CpanelRequests\Application\Session\SessionInterface;
use EliasHaeussler\CpanelRequests\Application\Session\WebSession;
use EliasHaeussler\CpanelRequests\Application\Traits\CookieAwareTrait;
use EliasHaeussler\CpanelRequests\Application\Traits\LogAwareTrait;
use EliasHaeussler\CpanelRequests\Exception\AuthenticationFailedException;
use EliasHaeussler\CpanelRequests\Exception\InactiveSessionException;
use EliasHaeussler\CpanelRequests\Exception\InvalidResponseDataException;
use EliasHaeussler\CpanelRequests\Exception\NotAuthenticatedException;
use EliasHaeussler\CpanelRequests\Exception\RequestFailedException;
use EliasHaeussler\CpanelRequests\Http\Protocol;
use EliasHaeussler\CpanelRequests\Http\Response\JsonResponse;
use EliasHaeussler\CpanelRequests\Http\Response\ResponseFactory;
use EliasHaeussler\CpanelRequests\Http\Response\ResponseInterface;
use EliasHaeussler\CpanelRequests\Http\UriBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OTPHP\TOTP;
use Psr\Http\Message\UriInterface;

/**
 * General cPanel class.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class CPanel implements ApplicationInterface
{
    use CookieAwareTrait;
    use LogAwareTrait;

    /**
     * @var string Session token parameter in API responses
     */
    public const SESSION_TOKEN_PARAMETER = 'security_token';

    /**
     * @var string Currently used protocol in request URIs
     */
    private $protocol;

    /**
     * @var string cPanel host
     */
    private $host = Protocol::HTTPS;

    /**
     * @var string cPanel user
     */
    private $username;

    /**
     * @var string cPanel password
     */
    private $password;

    /**
     * @var int cPanel port
     */
    private $port;

    /**
     * @var WebSession Authenticated cPanel session
     */
    private $session;

    /**
     * @var string|null TOTP secret for user authentication
     */
    private $otpSecret;

    /**
     * @var UriBuilder Uri builder used to generate URIs for this application
     */
    private $uriBuilder;

    /**
     * Initialize cPanel API communication.
     *
     * @param string $host cPanel host
     * @param string $username cPanel user
     * @param string $password cPanel password
     * @param int $port cPanel port
     * @param string $protocol Currently used protocol in request URIs
     * @param string|null $otpSecret OTP secret for user authentication
     */
    public function __construct(
        string $host,
        string $username,
        string $password,
        int $port = 2083,
        string $protocol = Protocol::HTTPS,
        string $otpSecret = null
    ) {
        // Store connection data
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->protocol = $protocol;

        // Store OTP secret
        if (is_string($otpSecret) && trim($otpSecret) !== '') {
            $this->otpSecret = $otpSecret;
        }

        // Initialize URI builder
        $this->uriBuilder = new UriBuilder($this->buildBaseUri());

        // Generate cookie file and log file
        $this->generateCookieIdentifier();
        $this->generateLogFile();
    }

    /**
     * @inheritDoc
     * @throws InactiveSessionException if session identifier does not belong to an active session
     * @throws InvalidResponseDataException if response data cannot be parsed as JSON
     */
    public function authorize(): ApplicationInterface
    {
        // Build authentication uri
        $path = 'login';
        $uriParams = [
            'login_only' => 1,
            'user' => $this->username,
            'pass' => $this->password,
        ];
        if ($this->isTwoFactorAuthenticationEnabled()) {
            $uriParams['tfa_token'] = $this->getTOTP();
        }
        $queryParams = http_build_query($uriParams);

        // Send authentication request
        $response = $this->request($path . '?' . $queryParams);

        // Throw exception if response is no JSON response
        if (!($response instanceof JsonResponse)) {
            throw new RequestFailedException(
                sprintf('Expected JSON response, got "%s" instead.', get_class($response)),
                1592850467
            );
        }

        // Throw exception if API response is not valid
        if (!$response->isValid(static::SESSION_TOKEN_PARAMETER)) {
            throw new AuthenticationFailedException(
                'Authentication failed. Please check your login credentials and try again.',
                1544739118
            );
        }

        // Store session
        $sessionIdentifier = (string)$response->getData()->{static::SESSION_TOKEN_PARAMETER};
        $this->session = new WebSession($sessionIdentifier, $this);

        return $this;
    }

    /**
     * @inheritDoc
     * @see https://documentation.cpanel.net/display/DD/Guide+to+UAPI
     */
    public function api(string $module, string $function, array $parameters = []): ResponseInterface
    {
        if (!$this->hasActiveSession()) {
            throw new NotAuthenticatedException(
                'Please authenticate first before sending API requests.',
                1544738659
            );
        }
        $uri = $this->buildApiRequestPath($module, $function, $parameters);
        return $this->request($uri);
    }

    public function getSession(): ?SessionInterface
    {
        return $this->session;
    }

    public function hasActiveSession(): bool
    {
        return $this->getSession() !== null && $this->session->isActive();
    }

    public function logout(): ApplicationInterface
    {
        if (!$this->hasActiveSession()) {
            return $this;
        }

        // Start logout process
        $response = $this->request('logout', [], false);

        // Throw exception if logout was not successful
        if (!$response->isValid()) {
            throw new RequestFailedException(
                'Error during logout. Make sure your session is still running.',
                1593257010
            );
        }

        // Reset session and cookies
        $this->session->setActive(false);
        $this->removeCookieFile();

        return $this;
    }

    public function request(string $path, array $options = [], bool $json = true): ResponseInterface
    {
        // Prepare logger
        $requestId = uniqid('request-');
        $logger = new Logger($requestId);
        $logger->pushHandler(new StreamHandler($this->logFile->getPathname(), Logger::WARNING));
        $stack = HandlerStack::create();
        $stack->push(Middleware::log($logger, new MessageFormatter()));

        // Prepare request options
        $cookieJar = new FileCookieJar($this->cookieFile->getPathname(), true);
        $defaultRequestOptions = [
            RequestOptions::COOKIES => $cookieJar,
            RequestOptions::HEADERS => [
                'Host' => $this->host,
                'Connection' => 'keep-alive',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'handler' => $stack,
        ];
        $options = array_merge_recursive($defaultRequestOptions, $options);

        // Add JSON flag
        if ($json) {
            $options[RequestOptions::HEADERS]['Accept'] = 'application/json';
        }

        // Send request
        try {
            $uri = $this->uriBuilder->withPath($path);
            $client = new Client($options);
            $response = $client->get($uri);
            return ResponseFactory::create($json ? 'json' : null, $response);
        } catch (RequestException | InvalidResponseDataException $e) {
            throw new RequestFailedException('Error during API request: ' . $e->getMessage(), 1589836385);
        }
    }

    /**
     * Generate base URI for requests to this application.
     *
     * @return UriInterface Generated base URI for requests to this application
     */
    private function buildBaseUri(): UriInterface
    {
        $uri = (new Uri())
            ->withScheme($this->protocol)
            ->withHost($this->host)
            ->withPort($this->port);
        return $uri;
    }

    /**
     * Build URI path for API requests.
     *
     * @param string $module cPanel module to be requested
     * @param string $function cPanel function to be requested
     * @param array $parameters Optionally provided parameters for API request
     * @return string Request URI path
     * @see https://documentation.cpanel.net/display/DD/Guide+to+UAPI
     */
    private function buildApiRequestPath(string $module, string $function, array $parameters = []): string
    {
        $path = implode('/', [$this->session->getIdentifier(), 'execute', $module, $function]);
        if ($parameters) {
            $query = http_build_query($parameters);
            $path .= '?' . $query;
        }
        return $path;
    }

    /**
     * Get current TOTP token if TOTP is enabled.
     *
     * @return string|null Current TOTP token
     */
    private function getTOTP(): ?string
    {
        return $this->isTwoFactorAuthenticationEnabled() ? TOTP::create($this->otpSecret)->now() : null;
    }

    /**
     * Check whether two factor authentication is enabled.
     *
     * @return bool `true` if two factor authentication is enabled, `false` otherwise
     */
    private function isTwoFactorAuthenticationEnabled(): bool
    {
        return $this->otpSecret !== null && trim($this->otpSecret) !== '';
    }
}
