<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\Tests\Unit\Http\Response;

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

use EliasHaeussler\CpanelRequests\Exception\InvalidResponseDataException;
use EliasHaeussler\CpanelRequests\Http\Response\NullResponse;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Class NullResponseTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class NullResponseTest extends TestCase
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var NullResponse
     */
    protected $subject;

    /**
     * @inheritDoc
     * @throws InvalidResponseDataException
     */
    protected function setUp(): void
    {
        $this->response = new Response();
        $this->subject = new NullResponse($this->response);
    }

    /**
     * @test
     */
    public function isValidReturnsAlwaysFalse(): void
    {
        static::assertFalse($this->subject->isValid());
    }

    /**
     * @test
     */
    public function getDataReturnsAlwaysNull(): void
    {
        static::assertNull($this->subject->getData());
    }

    /**
     * @test
     */
    public function getResponseReturnsResponseObject(): void
    {
        static::assertSame($this->response, $this->subject->getResponse());
    }
}
