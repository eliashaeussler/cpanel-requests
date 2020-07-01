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
 * JSON response from server.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class JsonResponse implements ResponseInterface
{
    /**
     * @var PsrResponse
     */
    private $response;

    /**
     * @var \stdClass
     */
    private $data;

    public function __construct(PsrResponse $response)
    {
        $this->response = $response;
        $this->data = $this->parseResponseData();
    }

    /**
     * @inheritDoc
     * @throws InvalidResponseDataException if response data cannot be parsed as JSON
     */
    public function isValid(string $dataKey = 'data'): bool
    {
        $data = $this->getData();
        return property_exists($data, 'status') && $data->status === 1 && property_exists($data, $dataKey);
    }

    /**
     * @inheritDoc
     * @throws InvalidResponseDataException if response data cannot be parsed as JSON
     */
    public function getData(): \stdClass
    {
        if ($this->data === null) {
            $this->data = $this->parseResponseData();
        }
        return $this->data;
    }

    public function getResponse(): PsrResponse
    {
        return $this->response;
    }

    /**
     * Parse response data as JSON.
     *
     * Parses the data from the current response and returns its object representation.
     *
     * @return \stdClass Parsed response data
     * @throws InvalidResponseDataException if response data cannot be parsed as JSON
     */
    private function parseResponseData(): \stdClass
    {
        $responseBody = (string)$this->response->getBody();
        $responseData = json_decode($responseBody);
        if ($responseData === null) {
            throw new InvalidResponseDataException(
                'Request failed. Please check the request URL and try again.',
                1544739719
            );
        }
        return $responseData;
    }
}
