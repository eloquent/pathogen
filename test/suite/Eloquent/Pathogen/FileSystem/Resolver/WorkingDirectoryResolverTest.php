<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem\Resolver;

use Eloquent\Pathogen\FileSystem\Factory\PlatformFileSystemPathFactory;
use Eloquent\Pathogen\Resolver\PathResolver;
use Phake;
use PHPUnit_Framework_TestCase;

class WorkingDirectoryResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->isolator = Phake::mock('Icecave\Isolator\Isolator');
        $this->factory = new PlatformFileSystemPathFactory(
            null,
            null,
            $this->isolator
        );
        $this->workingDirectoryPath = $this->factory->create('/foo/bar');
        $this->innerResolver = new PathResolver;
        $this->resolver = new WorkingDirectoryResolver(
            $this->workingDirectoryPath,
            $this->innerResolver,
            $this->factory,
            $this->isolator
        );

        Phake::when($this->isolator)
            ->defined('PHP_WINDOWS_VERSION_BUILD')
            ->thenReturn(false);
    }

    public function testConstructor()
    {
        $this->assertSame($this->workingDirectoryPath, $this->resolver->basePath());
        $this->assertSame($this->innerResolver, $this->resolver->resolver());
        $this->assertSame($this->factory, $this->resolver->factory());
    }

    public function testConstructorDefaults()
    {
        Phake::when($this->isolator)->getcwd()->thenReturn('/path/to/cwd');
        $this->resolver = new WorkingDirectoryResolver(
            null,
            null,
            null,
            $this->isolator
        );

        $this->assertInstanceOf(
            'Eloquent\Pathogen\AbsolutePath',
            $this->resolver->basePath()
        );
        $this->assertSame('/path/to/cwd', $this->resolver->basePath()->string());
        $this->assertInstanceOf(
            'Eloquent\Pathogen\Resolver\PathResolver',
            $this->resolver->resolver()
        );
        $this->assertInstanceOf(
            'Eloquent\Pathogen\FileSystem\Factory\PlatformFileSystemPathFactory',
            $this->resolver->factory()
        );
    }

    public function testResolve()
    {
        $path = $this->factory->create('baz/qux');
        $resolvedPath = $this->resolver->resolve($path);

        $this->assertSame('/foo/bar/baz/qux', $resolvedPath->string());
    }
}
