<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cpanel-requests".
 *
 * Copyright (C) 2020-2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CpanelRequests\Http\UriBuilder;

use EliasHaeussler\CpanelRequests\Helper;
use EliasHaeussler\CpanelRequests\Http;
use Psr\Http\Message;

use function http_build_query;
use function ltrim;
use function parse_str;

/**
 * DefaultUriBuilder.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class DefaultUriBuilder implements UriBuilderInterface
{
    public function buildUriForRequest(Http\Request\ApiRequest $request): Message\UriInterface
    {
        $path = Helper\UriHelper::mergePathSegments($request->getBaseUri(), [
            $request->getModule(),
            $request->getFunction(),
        ]);

        parse_str($request->getBaseUri()->getQuery(), $queryParams);

        return $request->getBaseUri()
            ->withPath('/'.ltrim($path, '/'))
            ->withQuery(http_build_query($queryParams + $request->getParameters()))
        ;
    }
}
