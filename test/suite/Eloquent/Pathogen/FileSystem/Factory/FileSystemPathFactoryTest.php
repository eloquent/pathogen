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
 * @covers \Eloquent\Pathogen\FileSystem\Factory\FileSystemPathFactory
 * @covers \Eloquent\Pathogen\FileSystem\Factory\AbstractFileSystemPathFactory
 */
class FileSystemPathFactoryTest extends PHPUnit_Framework_TestCase
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
        $this->factory = new FileSystemPathFactory(
            $this->posixFactory,
            $this->windowsFactory
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->posixFactory, $this->factory->posixFactory());
        $this->assertSame($this->windowsFactory, $this->factory->windowsFactory());
    }

    public function testConstructorDefaults()
    {
        $this->factory = new FileSystemPathFactory;

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
        $path = $this->factory->create('C:/foo/bar');

        $this->assertSame('C:/foo/bar', $path->string());
        $this->assertInstanceOf(
            '\Eloquent\Pathogen\Windows\AbsoluteWindowsPath',
            $path
        );
        Phake::verify($this->windowsFactory)->create('C:/foo/bar');
        Phake::verify($this->posixFactory, Phake::never())->create(
            Phake::anyParameters()
        );
    }

    public function testCreateFromAtoms()
    {
        $path = $this->factory->createFromAtoms(array('foo', 'bar'), false, false);

        $this->assertSame('foo/bar', $path->string());
        Phake::verify($this->posixFactory)->createFromAtoms(
            array('foo', 'bar'),
            false,
            false
        );
        Phake::verify($this->windowsFactory, Phake::never())->createFromAtoms(
            Phake::anyParameters()
        );
    }
}
