<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\Http;

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

use Psr\Http\Message\UriInterface;

/**
 * Class to build URIs from a given {@see UriInterface} instance.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class UriBuilder
{
    /**
     * @var UriInterface
     */
    private $baseUri;

    /**
     * Initialize a new URI builder from the given base URI.
     *
     * @param UriInterface $baseUri Base URI to be used for building URIs
     */
    public function __construct(UriInterface $baseUri)
    {
        $this->baseUri = $baseUri;
    }

    /**
     * Applies the given path to this URI and return its resulting URI.
     *
     * @param UriInterface|string $path Path to be applied to this URI, can bei either an
     *                                  {@see UriInterface} object or a string representation
     * @return UriInterface the resulting URI
     */
    public function withPath($path): UriInterface
    {
        if ($path instanceof UriInterface) {
            return $this->baseUri
                ->withPath($path->getPath())
                ->withQuery($path->getQuery() ?: $this->baseUri->getQuery())
                ->withFragment($path->getFragment() ?: $this->baseUri->getFragment());
        }
        if (!is_string($path)) {
            throw new \InvalidArgumentException(
                sprintf('URI path must be either %s or string, "%s" given.', UriInterface::class, gettype($path)),
                1592848070
            );
        }
        $parsedUrl = parse_url($path);
        return $this->baseUri
            ->withPath($parsedUrl['path'] ?? '')
            ->withQuery($parsedUrl['query'] ?? $this->baseUri->getQuery())
            ->withFragment($parsedUrl['fragment'] ?? $this->baseUri->getFragment());
    }
}
