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

namespace EliasHaeussler\CpanelRequests\Exception;

use EliasHaeussler\CpanelRequests\Http;
use Throwable;

use function sprintf;

/**
 * RequestFailedException.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class RequestFailedException extends Exception
{
    public static function create(Throwable $previousException): self
    {
        return new self(
            'Error during API request: '.$previousException->getMessage(),
            1589836385,
            $previousException,
        );
    }

    /**
     * @param class-string<Http\Response\ResponseInterface> $expected
     */
    public static function forUnexpectedResponse(string $expected, Http\Response\ResponseInterface $actual): self
    {
        return new self(
            sprintf('Expected "%s", got "%s" instead.', $expected, $actual::class),
            1592850467,
        );
    }
}
