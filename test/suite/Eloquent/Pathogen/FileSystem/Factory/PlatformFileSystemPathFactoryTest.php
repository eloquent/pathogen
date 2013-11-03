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

use Eloquent\Liberator\Liberator;
use PHPUnit_Framework_TestCase;
use Phake;

/**
 * @covers \Eloquent\Pathogen\FileSystem\Factory\PlatformFileSystemPathFactory
 * @covers \Eloquent\Pathogen\FileSystem\Factory\AbstractFileSystemPathFactory
 */
class PlatformFileSystemPathFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->unixFactory = Phake::partialMock(
            'Eloquent\Pathogen\Unix\Factory\UnixPathFactory'
        );
        $this->windowsFactory = Phake::partialMock(
            'Eloquent\Pathogen\Windows\Factory\WindowsPathFactory'
        );
        $this->isolator = Phake::mock('Icecave\Isolator\Isolator');
        $this->factory = new PlatformFileSystemPathFactory(
            $this->unixFactory,
            $this->windowsFactory,
            $this->isolator
        );

        Phake::when($this->isolator)->getcwd()->thenReturn('/path/to/cwd');
        Phake::when($this->isolator)->sys_get_temp_dir()->thenReturn('/path/to/tmp');
    }

    public function testConstructor()
    {
        $this->assertSame($this->unixFactory, $this->factory->unixFactory());
        $this->assertSame($this->windowsFactory, $this->factory->windowsFactory());
    }

    public function testConstructorDefaults()
    {
        $this->factory = new PlatformFileSystemPathFactory;

        $this->assertInstanceOf(
            'Eloquent\Pathogen\Unix\Factory\UnixPathFactory',
            $this->factory->unixFactory()
        );
        $this->assertInstanceOf(
            'Eloquent\Pathogen\Windows\Factory\WindowsPathFactory',
            $this->factory->windowsFactory()
        );
    }

    public function testCreateUnix()
    {
        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(false);
        $path = $this->factory->create('/foo/bar');

        $this->assertSame('/foo/bar', $path->string());
        $this->assertInstanceOf('Eloquent\Pathogen\AbsolutePath', $path);
        Phake::verify($this->unixFactory)->create('/foo/bar');
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
            'Eloquent\Pathogen\Windows\AbsoluteWindowsPath',
            $path
        );
        Phake::verify($this->windowsFactory)->create('/foo/bar');
        Phake::verify($this->unixFactory, Phake::never())->create(
            Phake::anyParameters()
        );
    }

    public function testCreateFromAtomsUnix()
    {
        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(false);
        $path = $this->factory->createFromAtoms(array('foo', 'bar'), false, false);

        $this->assertSame('foo/bar', $path->string());
        $this->assertInstanceOf('Eloquent\Pathogen\RelativePath', $path);
        Phake::verify($this->unixFactory)->createFromAtoms(
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
            'Eloquent\Pathogen\Windows\RelativeWindowsPath',
            $path
        );
        Phake::verify($this->windowsFactory)->createFromAtoms(
            array('foo', 'bar'),
            false,
            false
        );
        Phake::verify($this->unixFactory, Phake::never())->createFromAtoms(
            Phake::anyParameters()
        );
    }

    public function testCreateWorkingDirectoryPathUnix()
    {
        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(false);
        $path = $this->factory->createWorkingDirectoryPath();

        $this->assertSame('/path/to/cwd', $path->string());
        $this->assertInstanceOf('Eloquent\Pathogen\AbsolutePath', $path);
        Phake::verify($this->unixFactory)->create('/path/to/cwd');
        Phake::verify($this->windowsFactory, Phake::never())->create(
            Phake::anyParameters()
        );
    }

    public function testCreateWorkingDirectoryPathWindows()
    {
        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(true);
        $path = $this->factory->createWorkingDirectoryPath();

        $this->assertSame('/path/to/cwd', $path->string());
        $this->assertInstanceOf(
            'Eloquent\Pathogen\Windows\AbsoluteWindowsPath',
            $path
        );
        Phake::verify($this->windowsFactory)->create('/path/to/cwd');
        Phake::verify($this->unixFactory, Phake::never())->create(
            Phake::anyParameters()
        );
    }

    public function testCreateTemporaryDirectoryPathUnix()
    {
        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(false);
        $path = $this->factory->createTemporaryDirectoryPath();

        $this->assertSame('/path/to/tmp', $path->string());
        $this->assertInstanceOf('Eloquent\Pathogen\AbsolutePath', $path);
        Phake::verify($this->unixFactory)->create('/path/to/tmp');
        Phake::verify($this->windowsFactory, Phake::never())->create(
            Phake::anyParameters()
        );
    }

    public function testCreateTemporaryDirectoryPathWindows()
    {
        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(true);
        $path = $this->factory->createTemporaryDirectoryPath();

        $this->assertSame('/path/to/tmp', $path->string());
        $this->assertInstanceOf(
            'Eloquent\Pathogen\Windows\AbsoluteWindowsPath',
            $path
        );
        Phake::verify($this->windowsFactory)->create('/path/to/tmp');
        Phake::verify($this->unixFactory, Phake::never())->create(
            Phake::anyParameters()
        );
    }

    public function testCreateTemporaryPathUnix()
    {
        Phake::when($this->isolator)->uniqid('', true)->thenReturn('unique-id');
        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(false);
        $path = $this->factory->createTemporaryPath();

        $this->assertSame('/path/to/tmp/unique-id', $path->string());
        $this->assertInstanceOf('Eloquent\Pathogen\AbsolutePath', $path);
        Phake::verify($this->unixFactory)->create('/path/to/tmp');
        Phake::verify($this->windowsFactory, Phake::never())->create(
            Phake::anyParameters()
        );
    }

    public function testCreateTemporaryPathWindows()
    {
        Phake::when($this->isolator)->uniqid('', true)->thenReturn('unique-id');
        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(true);
        $path = $this->factory->createTemporaryPath();

        $this->assertSame('/path/to/tmp/unique-id', $path->string());
        $this->assertInstanceOf(
            'Eloquent\Pathogen\Windows\AbsoluteWindowsPath',
            $path
        );
        Phake::verify($this->windowsFactory)->create('/path/to/tmp');
        Phake::verify($this->unixFactory, Phake::never())->create(
            Phake::anyParameters()
        );
    }

    public function testCreateTemporaryPathWithPrefix()
    {
        Phake::when($this->isolator)->uniqid('foo-', true)->thenReturn('foo-unique-id');
        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(false);
        $path = $this->factory->createTemporaryPath('foo-');

        $this->assertSame('/path/to/tmp/foo-unique-id', $path->string());
        $this->assertInstanceOf('Eloquent\Pathogen\AbsolutePath', $path);
        Phake::verify($this->unixFactory)->create('/path/to/tmp');
        Phake::verify($this->windowsFactory, Phake::never())->create(
            Phake::anyParameters()
        );
    }

    public function testInstance()
    {
        $class = Liberator::liberateClass(__NAMESPACE__ . '\PlatformFileSystemPathFactory');
        $class->instance = null;
        $actual = PlatformFileSystemPathFactory::instance();

        $this->assertInstanceOf(__NAMESPACE__ . '\PlatformFileSystemPathFactory', $actual);
        $this->assertSame($actual, PlatformFileSystemPathFactory::instance());
    }
}
