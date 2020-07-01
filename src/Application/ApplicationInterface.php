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
use EliasHaeussler\CpanelRequests\Exception\AuthenticationFailedException;
use EliasHaeussler\CpanelRequests\Exception\NotAuthenticatedException;
use EliasHaeussler\CpanelRequests\Exception\RequestFailedException;
use EliasHaeussler\CpanelRequests\Http\Response\ResponseInterface;

/**
 Interface describing a single application.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
interface ApplicationInterface
{
    /**
     * Authorize selected user at this application and start session.
     * 
     * Starts authorization for selected user at currently used application. The
     * resulting session should be stored in this application instance in order
     * to send further API requests.
     *
     * @throws AuthenticationFailedException if authorization failed with selected user
     * @throws RequestFailedException if result of authorization request is invalid
     * @return self This cPanel instance
     */
    public function authorize(): self;

    /**
     * Quit currently active application session.
     * 
     * Starts the logout process of the current session at this application. The result
     * should be that no more active sessions are attached to this application instance.
     *
     * @throws RequestFailedException if exception occurs during logout request
     */
    public function logout(): self;

    /**
     * Get currently active session.
     * 
     * Returns the currently active session, if any session has been started, or NULL.
     * 
     * @return SessionInterface|null Currently active session, if available, `NULL` otherwise
     */
    public function getSession(): ?SessionInterface;

    /**
     * Check whether application has active session.
     *
     * Returns `true` if the currently used application has an opened session
     * which is currently active.
     *
     * @return bool `true` if application has active session, `false` otherwise
     */
    public function hasActiveSession(): bool;

    /**
     * Make API request at this application and return the API response.
     *
     * Sends an API request to the currently used application and returns the
     * resulting API response. Note that for this method to work an active
     * session is required. This can be achieved by calling {@see authorize}
     * prior to sending API requests.
     *
     * @param string $module The application module to be requested
     * @param string $function The module function to be requested
     * @param array $parameters Optional parameters to be passed to the application module
     * @return ResponseInterface API response object
     * @throws NotAuthenticatedException if authentication progress is not done yet
     * @throws RequestFailedException if result of API request is invalid
     */
    public function api(string $module, string $function, array $parameters): ResponseInterface;

    /**
     * Send an request to the current application.
     *
     * Sends a request to the current application and returns the response.
     *
     * @param string $path Application request path
     * @param array $options Additional request options, will be merged with default options
     * @return ResponseInterface Response from application
     * @throws RequestFailedException if application request fails
     */
    public function request(string $path, array $options = []): ResponseInterface;
}
