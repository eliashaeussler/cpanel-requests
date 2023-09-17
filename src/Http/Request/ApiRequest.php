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

namespace EliasHaeussler\CpanelRequests\Http\Request;

use Psr\Http\Message;

/**
 * ApiRequest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ApiRequest
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private readonly Message\UriInterface $baseUri,
        private readonly string $module,
        private readonly ?string $function = null,
        private array $parameters = [],
    ) {}

    public function getBaseUri(): Message\UriInterface
    {
        return $this->baseUri;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getFunction(): ?string
    {
        return $this->function;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function addParameter(string $name, mixed $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function removeParameter(string $name): self
    {
        unset($this->parameters[$name]);

        return $this;
    }
}
