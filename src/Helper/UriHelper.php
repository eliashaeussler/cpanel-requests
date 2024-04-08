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

namespace EliasHaeussler\CpanelRequests\Helper;

use Psr\Http\Message;

use function is_scalar;
use function trim;

/**
 * UriHelper.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class UriHelper
{
    /**
     * @param list<mixed> $paths
     */
    public static function mergePathSegments(Message\UriInterface $uri, array $paths): string
    {
        $basePath = $uri->getPath();
        $pathSegments = [...explode('/', $basePath), ...$paths];

        return implode('/', array_filter($pathSegments, self::isValidPathSegment(...)));
    }

    private static function isValidPathSegment(mixed $pathSegment): bool
    {
        if (!is_scalar($pathSegment)) {
            return false;
        }

        return '' !== trim((string) $pathSegment);
    }
}
