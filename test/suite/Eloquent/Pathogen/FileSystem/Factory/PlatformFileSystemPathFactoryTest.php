<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem\Factory;

use Phake;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Pathogen\FileSystem\Factory\PlatformFileSystemPathFactory
 * @covers \Eloquent\Pathogen\FileSystem\Factory\AbstractFileSystemPathFactory
 */
class PlatformFileSystemPathFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->posixFactory = Phake::partialMock(
            '\Eloquent\Pathogen\Factory\PathFactory'
        );
        $this->windowsFactory = Phake::partialMock(
            '\Eloquent\Pathogen\Windows\Factory\WindowsPathFactory'
        );
        $this->isolator = Phake::mock('Icecave\Isolator\Isolator');
        $this->factory = new PlatformFileSystemPathFactory(
            $this->posixFactory,
            $this->windowsFactory,
            $this->isolator
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->posixFactory, $this->factory->posixFactory());
        $this->assertSame($this->windowsFactory, $this->factory->windowsFactory());
    }

    public function testConstructorDefaults()
    {
        $this->factory = new PlatformFileSystemPathFactory;

        $this->assertInstanceOf(
            '\Eloquent\Pathogen\Factory\PathFactory',
            $this->factory->posixFactory()
        );
        $this->assertInstanceOf(
            '\Eloquent\Pathogen\Windows\Factory\WindowsPathFactory',
            $this->factory->windowsFactory()
        );
    }

    public function testCreatePosix()
    {
        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(false);
        $path = $this->factory->create('/foo/bar');

        $this->assertSame('/foo/bar', $path->string());
        $this->assertInstanceOf('\Eloquent\Pathogen\AbsolutePath', $path);
        Phake::verify($this->posixFactory)->create('/foo/bar');
        Phake::verify($this->windowsFactory, Phake::never())->create(
            Phake::anyParameters()
        );
    }

    public function testCreateWindows()
    {
        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(true);
        $path = $this->factory->create('/foo/bar');

        $this->assertSame('/foo/bar', $path->string());
        $this->assertInstanceOf(
            '\Eloquent\Pathogen\Windows\AbsoluteWindowsPath',
            $path
        );
        Phake::verify($this->windowsFactory)->create('/foo/bar');
        Phake::verify($this->posixFactory, Phake::never())->create(
            Phake::anyParameters()
        );
    }

    public function testCreateFromAtomsPosix()
    {
        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(false);
        $path = $this->factory->createFromAtoms(array('foo', 'bar'), false, false);

        $this->assertSame('foo/bar', $path->string());
        $this->assertInstanceOf('\Eloquent\Pathogen\RelativePath', $path);
        Phake::verify($this->posixFactory)->createFromAtoms(
            array('foo', 'bar'),
            false,
            false
        );
        Phake::verify($this->windowsFactory, Phake::never())->createFromAtoms(
            Phake::anyParameters()
        );
    }

    public function testCreateFromAtomsWindows()
    {
        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(true);
        $path = $this->factory->createFromAtoms(array('foo', 'bar'), false, false);

        $this->assertSame('foo/bar', $path->string());
        $this->assertInstanceOf(
            '\Eloquent\Pathogen\Windows\RelativeWindowsPath',
            $path
        );
        Phake::verify($this->windowsFactory)->createFromAtoms(
            array('foo', 'bar'),
            false,
            false
        );
        Phake::verify($this->posixFactory, Phake::never())->createFromAtoms(
            Phake::anyParameters()
        );
    }
}
