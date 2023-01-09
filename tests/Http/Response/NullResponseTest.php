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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\CpanelRequests\Tests\Http\Response;

use EliasHaeussler\CpanelRequests\Http;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;
use Psr\Http\Message;

/**
 * NullResponseTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class NullResponseTest extends Framework\TestCase
{
    private Message\ResponseInterface $response;
    private Http\Response\NullResponse $subject;

    protected function setUp(): void
    {
        $this->response = new Psr7\Response();
        $this->subject = new Http\Response\NullResponse($this->response);
    }

    /**
     * @test
     */
    public function supportsReturnsAlwaysTrue(): void
    {
        self::assertTrue(Http\Response\NullResponse::supports($this->response));
    }

    /**
     * @test
     */
    public function isValidReturnsAlwaysFalse(): void
    {
        self::assertFalse($this->subject->isValid());
    }

    /**
     * @test
     */
    public function getDataReturnsAlwaysNull(): void
    {
        self::assertNull($this->subject->getData());
    }

    /**
     * @test
     */
    public function getResponseReturnsResponseObject(): void
    {
        self::assertSame($this->response, $this->subject->getOriginalResponse());
    }
}
