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
 * Factory to build response representations from server responses.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class ResponseFactory
{
    private static $mapping = [
        'json' => JsonResponse::class,
        'web' => WebResponse::class,
    ];

    private static $default = 'web';

    /**
     * Create response from type or server response object.
     *
     * Returns a new representation of the given server response object. Determination of the
     * resulting object will be done on basis of the given type. Otherwise, the default
     * representation will be returned.
     *
     * @param string|null $type Server response type, e.g. `json` or `web` (default)
     * @param PsrResponse $response Server response object
     * @return ResponseInterface Representation of the given server response
     * @throws InvalidResponseDataException if response data cannot be parsed
     */
    public static function create(?string $type, PsrResponse $response): ResponseInterface
    {
        $type = $type ?? static::$default;
        $normalizedType = strtolower(trim($type));
        if (!array_key_exists($normalizedType, static::$mapping)) {
            return static::makeInstance(NullResponse::class, $response);
        }
        $responseType = static::$mapping[$normalizedType];
        return static::makeInstance($responseType, $response);
    }

    /**
     * Create response from server response object.
     *
     * Returns a new representation of the given server response object. Determination of the
     * resulting object will be done on basis of the given response object only. For this,
     * the response type will be extracted from the response object.
     *
     * @param PsrResponse $response Server response object
     * @return ResponseInterface Representation of the given server response
     * @throws InvalidResponseDataException if response data cannot be parsed
     */
    public static function createFromResponse(PsrResponse $response): ResponseInterface
    {
        $type = static::extractTypeFromResponse($response);
        return static::create($type, $response);
    }

    /**
     * Transform server response object to response type.
     *
     * Returns the resulting type of the given server response object. This can be either
     * JSON or a default web response.
     *
     * @param PsrResponse $response Server response object
     * @return string Transformed response type, either `json` or `web`
     */
    private static function extractTypeFromResponse(PsrResponse $response): string
    {
        // Check for JSON response
        if (
            ($response->hasHeader('Accept') && $response->getHeader('Accept')[0] ?? null === 'application/json') ||
            ($response->hasHeader('Content-Type') && $response->getHeader('Content-Type')[0] ?? null === 'application/json')
        ) {
            return 'json';
        }
        return static::$default;
    }

    /**
     * Instantiate response representation for given server response object.
     *
     * Creates a new representation of the given server response object by the given class
     * name. If the class is invalid, e.g. if it does not exist or does not implement the
     * {@see ResponseInterface}, a representation of {@see NullResponse} will be returned.
     *
     * @param string $className Class name of the response representation to be initialized
     * @param PsrResponse $response Server response object
     * @return ResponseInterface Representation of the given server response object, can be
     *                           an instance of {@see NullResponse} if the given class name
     *                           is not valid
     * @throws InvalidResponseDataException if response data cannot be parsed
     */
    private static function makeInstance(string $className, PsrResponse $response): ResponseInterface
    {
        if (!class_exists($className)) {
            return new NullResponse($response);
        }
        if (!in_array(ResponseInterface::class, class_implements($className), true)) {
            return new NullResponse($response);
        }
        return new $className($response);
    }
}
