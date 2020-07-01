<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\Http\Response;

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

use EliasHaeussler\CpanelRequests\Exception\InvalidResponseDataException;
use Psr\Http\Message\ResponseInterface as PsrResponse;

/**
 * Interface describing response representations.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
interface ResponseInterface
{
    /**
     * Initialize and parse a given server response.
     *
     * Initializes a new response object by a given server response and parses the response data.
     *
     * @param PsrResponse $response Response from server
     * @throws InvalidResponseDataException if response data cannot be parsed
     */
    public function __construct(PsrResponse $response);

    /**
     * Check whether server returned a valid response.
     *
     * Determines whether the server response is valid and returns the result.
     *
     * @return bool `true` if server response is valid, `false` otherwise
     */
    public function isValid(): bool;

    /**
     * Get parsed server response data.
     *
     * Returns the parsed data from the current server response.
     *
     * @return mixed Parsed response data
     */
    public function getData();

    /**
     * Get current server response object.
     *
     * Returns the current server response object.
     *
     * @return PsrResponse Current server response object
     */
    public function getResponse(): PsrResponse;
}
