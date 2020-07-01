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

use EliasHaeussler\CpanelRequests\Exception\InactiveSessionException;

/**
 * Interface describing a session of a single application.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
interface SessionInterface
{
    /**
     * Start a new session of this type.
     *
     * Starts a new session of this session type and returns this instance.
     *
     * @return self This session type instance
     */
    public function start(): self;

    /**
     * Stop the currently active session.
     *
     * Stops the currently active session of this session type and returns
     * this instance.
     *
     * @throws InactiveSessionException if no session is active currently
     * @return self This session type instance
     */
    public function stop(): self;

    /**
     * Check whether a session is currently active.
     *
     * Returns `true` if there's currently an active session associated to
     * this session type. Otherwise, `false` is returned.
     *
     * @return bool `true` if this session type is associated with an active session, `false` otherwise
     */
    public function isActive(): bool;
}
