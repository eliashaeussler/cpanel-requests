<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/cpanel-requests".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\CpanelRequests\Http\Response;

use EliasHaeussler\CpanelRequests\Exception;
use Psr\Http\Message\ResponseInterface as PsrResponse;

use function array_key_exists;

/**
 * ResponseFactory.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ResponseFactory
{
    /**
     * @var non-empty-array<string, class-string<ResponseInterface>>
     */
    private array $map = [
        'json' => JsonResponse::class,
        'web' => WebResponse::class,
    ];
    private string $default = 'web';

    /**
     * @throws Exception\InvalidResponseDataException
     */
    public function create(string $type, PsrResponse $response): ResponseInterface
    {
        $normalizedType = strtolower(trim($type));

        if ($this->supports($normalizedType)) {
            $className = $this->map[$normalizedType];
        } else {
            $className = NullResponse::class;
        }

        return $this->make($className, $response);
    }

    /**
     * @throws Exception\InvalidResponseDataException
     */
    public function createFromResponse(PsrResponse $response): ResponseInterface
    {
        $selectedType = $this->default;

        /** @var ResponseInterface $className */
        foreach ($this->map as $type => $className) {
            if ($className::supports($response)) {
                $selectedType = $type;
                break;
            }
        }

        return $this->create($selectedType, $response);
    }

    public function supports(string $type): bool
    {
        return array_key_exists($type, $this->map);
    }

    /**
     * @param class-string<ResponseInterface> $className
     *
     * @throws Exception\InvalidResponseDataException
     */
    private function make(string $className, PsrResponse $response): ResponseInterface
    {
        // @codeCoverageIgnoreStart
        if (!class_exists($className)) {
            return new NullResponse($response);
        }
        // @codeCoverageIgnoreEnd

        return new $className($response);
    }
}
