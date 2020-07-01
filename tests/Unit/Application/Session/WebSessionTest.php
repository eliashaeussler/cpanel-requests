<?php
declare(strict_types=1);
namespace EliasHaeussler\CpanelRequests\Tests\Unit\Application\Session;

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
use EliasHaeussler\CpanelRequests\Application\Session\WebSession;
use EliasHaeussler\CpanelRequests\Exception\AuthenticationFailedException;
use EliasHaeussler\CpanelRequests\Exception\InactiveSessionException;
use EliasHaeussler\CpanelRequests\Exception\RequestFailedException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Class WebSessionTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class WebSessionTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|ApplicationInterface
     */
    protected $applicationProphecy;

    /**
     * @var WebSession
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->applicationProphecy = $this->prophesize(ApplicationInterface::class);
        $this->subject = new WebSession('foo', $this->applicationProphecy->reveal());
    }

    /**
     * @test
     */
    public function canCreateWebSession(): void
    {
        static::assertInstanceOf(WebSession::class, $this->subject);
        static::assertTrue($this->subject->isActive());
        static::assertSame('foo', $this->subject->getIdentifier());
    }

    /**
     * @test
     * @dataProvider constructorThrowsExceptionIfInvalidIdentifierIsGivenDataProvider
     * @param string $identifier
     * @throws InactiveSessionException
     */
    public function constructorThrowsExceptionIfInvalidIdentifierIsGiven(string $identifier): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1592848476);
        new WebSession($identifier, $this->applicationProphecy->reveal());
    }

    /**
     * @test
     * @throws AuthenticationFailedException
     * @throws RequestFailedException
     */
    public function startReturnsCurrentSessionIfSessionIsAlreadyActive(): void
    {
        $this->applicationProphecy->authorize()->shouldNotBeCalled();
        static::assertSame($this->subject, $this->subject->start());
    }

    /**
     * @test
     * @throws AuthenticationFailedException
     * @throws InactiveSessionException
     * @throws RequestFailedException
     */
    public function startReturnsNewSessionIfSessionIsCurrentlyNotActive(): void
    {
        $this->subject->setActive(false);
        $newSession = new WebSession('baz', $this->applicationProphecy->reveal());

        $this->applicationProphecy->authorize()->willReturn($this->applicationProphecy->reveal())->shouldBeCalledOnce();
        $this->applicationProphecy->getSession()->willReturn($newSession)->shouldBeCalledOnce();
        static::assertSame($newSession, $this->subject->start());
    }

    /**
     * @test
     * @throws RequestFailedException
     * @throws InactiveSessionException
     */
    public function stopReturnsCurrentSessionIfSessionIsAlreadyStopped(): void
    {
        $this->subject->setActive(false);

        $this->applicationProphecy->logout()->shouldNotBeCalled();
        static::assertFalse($this->subject->isActive());
        static::assertSame($this->subject, $this->subject->stop());
        static::assertFalse($this->subject->isActive());
    }

    /**
     * @test
     * @throws RequestFailedException
     * @throws InactiveSessionException
     */
    public function stopTriggersApplicationLogoutProcessIfSessionIsCurrentlyActive(): void
    {
        $this->applicationProphecy->logout()->shouldBeCalledOnce();
        static::assertTrue($this->subject->isActive());
        static::assertSame($this->subject, $this->subject->stop());
        static::assertFalse($this->subject->isActive());
    }

    /**
     * @test
     */
    public function setIdentifierAllowsUpdatingTheSessionIdentifier(): void
    {
        $newSessionIdentifier = 'hello-world';

        $this->subject->setIdentifier($newSessionIdentifier);

        static::assertSame($newSessionIdentifier, $this->subject->getIdentifier());
    }

    public function constructorThrowsExceptionIfInvalidIdentifierIsGivenDataProvider(): array
    {
        return [
            'empty identifier' => [
                '',
            ],
            'whitespaces only' => [
                '      ',
            ],
        ];
    }
}
