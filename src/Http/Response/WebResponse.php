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

use Psr\Http\Message\ResponseInterface as PsrResponse;

/**
 * A classical web response from server.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class WebResponse implements ResponseInterface
{
    /**
     * @var PsrResponse
     */
    private $response;

    public function __construct(PsrResponse $response)
    {
        $this->response = $response;
    }

    public function isValid(): bool
    {
        return $this->response->getStatusCode() < 400;
    }

    public function getData(): string
    {
        return (string)$this->response->getBody();
    }

    public function getResponse(): PsrResponse
    {
        return $this->response;
    }
}
