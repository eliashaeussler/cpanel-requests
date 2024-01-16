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

namespace EliasHaeussler\CpanelRequests\Tests\Application\Authorization;

use EliasHaeussler\CpanelRequests\Application;
use EliasHaeussler\CpanelRequests\Http;
use EliasHaeussler\CpanelRequests\Tests;
use PHPUnit\Framework;

/**
 * TokenAuthorizationTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class TokenAuthorizationTest extends Tests\MockServerAwareTestCase
{
    private Application\Authorization\TokenAuthorization $subject;

    protected function setUp(): void
    {
        $this->subject = new Application\Authorization\TokenAuthorization('foo', 'bar');
    }

    #[Framework\Attributes\Test]
    public function sendAuthorizedRequestAuthorizesAndSendsGivenRequest(): void
    {
        $request = new Http\Request\ApiRequest(self::getMockServerBaseUri(), 'foo');

        $this->subject->sendAuthorizedRequest('GET', $request);

        $lastRequest = self::getLastMockServerRequest();
        $requestHeaders = $lastRequest->getHeaders();

        self::assertArrayHasKey('Authorization', $requestHeaders);
        self::assertSame('cpanel foo:bar', $requestHeaders['Authorization']);
    }
}
