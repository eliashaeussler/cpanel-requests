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

namespace EliasHaeussler\CpanelRequests\Http\Response;

use EliasHaeussler\CpanelRequests\Exception;
use JsonException;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use stdClass;

use function json_decode;
use function str_starts_with;

/**
 * JsonResponse.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class JsonResponse implements ResponseInterface
{
    private const MIME_TYPE = 'application/json';
    private const POSSIBLE_HEADERS = [
        'Accept',
        'Content-Type',
    ];

    private readonly stdClass $data;

    public function __construct(
        private readonly PsrResponse $response,
    ) {
        $this->data = $this->parseResponseData();
    }

    public static function supports(PsrResponse $response): bool
    {
        // Test for expected mime type in response headers
        foreach (self::POSSIBLE_HEADERS as $header) {
            if ($response->hasHeader($header) && str_starts_with($response->getHeader($header)[0], self::MIME_TYPE)) {
                return true;
            }
        }

        // Fetch response body
        $body = $response->getBody();
        $content = (string) $body;
        $body->rewind();

        // Try to JSON-decode response body
        try {
            $json = json_decode($content, false, 512, JSON_THROW_ON_ERROR);

            return $json instanceof stdClass;
        } catch (JsonException) {
            return false;
        }
    }

    public function isValid(string $dataKey = 'data'): bool
    {
        return property_exists($this->data, 'status')
            && 1 === $this->data->status
            && property_exists($this->data, $dataKey)
        ;
    }

    public function getData(): stdClass
    {
        return $this->data;
    }

    public function getOriginalResponse(): PsrResponse
    {
        return $this->response;
    }

    /**
     * @throws Exception\InvalidResponseDataException
     */
    private function parseResponseData(): stdClass
    {
        $responseBody = (string) $this->response->getBody();
        $responseData = json_decode($responseBody, flags: JSON_THROW_ON_ERROR);

        if (!($responseData instanceof stdClass)) {
            throw Exception\InvalidResponseDataException::create();
        }

        return $responseData;
    }
}
