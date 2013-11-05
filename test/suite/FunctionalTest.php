<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Eloquent\Pathogen\Factory\PathFactory;
use Eloquent\Pathogen\FileSystem\Factory\PlatformFileSystemPathFactory;
use Eloquent\Pathogen\Path;

class FunctionalTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->server = $_SERVER;
    }

    protected function tearDown()
    {
        parent::tearDown();

        $_SERVER = $this->server;
    }

    public function testConsumerTrait()
    {
        if (!defined('T_TRAIT')) {
            $this->markTestSkipped('Requires trait support');
        }

        $this->expectOutputString('Eloquent\Pathogen\FileSystem\Factory\FileSystemPathFactory');

        $consumer = new ExampleConsumer;
        echo get_class($consumer->pathFactory()); // outputs 'Eloquent\Pathogen\FileSystem\Factory\FileSystemPathFactory'
    }

    public function testResolveAgainstWorkingDirectory()
    {
        $_SERVER['argv'] = array('command', 'path/to/foo');

        $factory = new PlatformFileSystemPathFactory;
        $workingDirectoryPath = $factory->createWorkingDirectoryPath();

        $path = $workingDirectoryPath->resolve(
            $factory->create($_SERVER['argv'][1])
        );

        $this->assertSame(getcwd() . '/path/to/foo', $path->string());
    }

    public function testResolveArbitrary()
    {
        $this->expectOutputString("/path/to/base/../child\n/path/to/child");

        $basePath = Path::fromString('/path/to/base');
        $path = Path::fromString('../child');

        $resolvedPath = $basePath->resolve($path);

        echo $resolvedPath->string();              // outputs '/path/to/base/../child'
        echo "\n";
        echo $resolvedPath->normalize()->string(); // outputs '/path/to/child'
    }

    public function testDetermineAncestor()
    {
        $this->expectOutputString(
            'bool(true)' . PHP_EOL . 'bool(false)' . PHP_EOL
        );

        $basePath = Path::fromString('/path/to/foo');
        $pathA = Path::fromString('/path/to/foo/bar');
        $pathB = Path::fromString('/path/to/somewhere/else');

        var_dump($basePath->isAncestorOf($pathA)); // outputs 'bool(true)'
        var_dump($basePath->isAncestorOf($pathB)); // outputs 'bool(false)'
    }

    public function testAppendExtension()
    {
        $this->expectOutputString('/path/to/foo.bar.baz');

        $path = Path::fromString('/path/to/foo.bar');
        $pathWithExtension = $path->joinExtensions('baz');

        echo $pathWithExtension->string(); // outputs '/path/to/foo.bar.baz'
    }

    public function testReplaceExtension()
    {
        $this->expectOutputString('/path/to/foo.baz');

        $path = Path::fromString('/path/to/foo.bar');
        $pathWithNewExtension = $path->replaceExtension('baz');

        echo $pathWithNewExtension->string(); // outputs '/path/to/foo.baz'
    }

    public function testReplacePathSection()
    {
        $this->expectOutputString('/path/for/baz/bar');

        $path = Path::fromString('/path/to/foo/bar');
        $pathWithReplacement = $path->replace(1, array('for', 'baz'), 2);

        echo $pathWithReplacement->string(); // outputs '/path/for/baz/bar'
    }
}
