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

namespace EliasHaeussler\CpanelRequests\Tests\Http\Request;

use EliasHaeussler\CpanelRequests\Http;
use GuzzleHttp\Psr7;
use PHPUnit\Framework;

/**
 * ApiRequestTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ApiRequestTest extends Framework\TestCase
{
    private Psr7\Uri $baseUri;
    private Http\Request\ApiRequest $subject;

    protected function setUp(): void
    {
        $this->baseUri = new Psr7\Uri('http://example.org');
        $this->subject = new Http\Request\ApiRequest($this->baseUri, 'foo', 'bar', ['hello' => 'world']);
    }

    /**
     * @test
     */
    public function getBaseUriReturnsBaseUri(): void
    {
        self::assertSame($this->baseUri, $this->subject->getBaseUri());
    }

    /**
     * @test
     */
    public function getModuleReturnsModule(): void
    {
        self::assertSame('foo', $this->subject->getModule());
    }

    /**
     * @test
     */
    public function getFunctionReturnsFunction(): void
    {
        self::assertSame('bar', $this->subject->getFunction());
    }

    /**
     * @test
     */
    public function getParametersReturnsParameters(): void
    {
        self::assertSame(['hello' => 'world'], $this->subject->getParameters());
    }

    /**
     * @test
     */
    public function setParametersAppliesGivenParameters(): void
    {
        $this->subject->setParameters(['foo' => 'bar']);

        self::assertSame(['foo' => 'bar'], $this->subject->getParameters());
    }

    /**
     * @test
     */
    public function addParameterAddsGivenParameter(): void
    {
        $this->subject->addParameter('foo', 'bar');

        self::assertSame(['hello' => 'world', 'foo' => 'bar'], $this->subject->getParameters());
    }

    /**
     * @test
     */
    public function removeParameterRemovesGivenParameter(): void
    {
        $this->subject->removeParameter('foo');

        self::assertSame(['hello' => 'world'], $this->subject->getParameters());

        $this->subject->removeParameter('hello');

        self::assertSame([], $this->subject->getParameters());
    }
}
