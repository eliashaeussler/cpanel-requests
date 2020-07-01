<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\Application\Session;

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

use EliasHaeussler\CpanelRequests\Application\ApplicationInterface;
use EliasHaeussler\CpanelRequests\Exception\InactiveSessionException;
use EliasHaeussler\CpanelRequests\Exception\RequestFailedException;

/**
 * Representation of a web session of a single application.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class WebSession implements SessionInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var ApplicationInterface
     */
    private $application;

    /**
     * @var bool
     */
    private $active;

    /**
     * @param string $identifier
     * @param ApplicationInterface $application
     * @param bool $active
     * @throws InactiveSessionException if session identifier does not belong to an active session
     */
    public function __construct(string $identifier, ApplicationInterface $application, bool $active = true)
    {
        $this->identifier = $identifier;
        $this->application = $application;
        $this->active = $active;
        $this->validateIdentifier();
    }

    public function start(): SessionInterface
    {
        if ($this->active) {
            return $this;
        }
        return $this->application->authorize()->getSession();
    }

    /**
     * @inheritDoc
     * @throws RequestFailedException if exception occurs during application logout request
     */
    public function stop(): SessionInterface
    {
        if ($this->active) {
            $this->application->logout();
            $this->active = false;
        }
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): WebSession
    {
        $this->active = $active;
        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Validate session identifier by predefined rule set.
     *
     * @throws InactiveSessionException if given session identifier is invalid
     */
    protected function validateIdentifier(): void
    {
        if ($this->identifier === null) {
            throw new InactiveSessionException('No session identifier given.', 1592848420);
        }
        if (!is_string($this->identifier)) {
            throw new \InvalidArgumentException(
                sprintf('Session identifier must be a valid string, "%s" given.', gettype($this->identifier)),
                1592848455
            );
        }
        if (trim($this->identifier) === '') {
            throw new \InvalidArgumentException('Session identifier must not be empty.', 1592848476);
        }
    }
}
