<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem\Normalizer;

use Eloquent\Liberator\Liberator;
use Eloquent\Pathogen\AbsolutePath;
use Eloquent\Pathogen\FileSystem\Factory\FileSystemPathFactory;
use Eloquent\Pathogen\Unix\Normalizer\UnixPathNormalizer;
use Eloquent\Pathogen\Windows\Normalizer\WindowsPathNormalizer;
use PHPUnit_Framework_TestCase;

class FileSystemPathNormalizerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new FileSystemPathFactory;

        $this->unixNormalizer = new UnixPathNormalizer;
        $this->windowsNormalizer = new WindowsPathNormalizer;
        $this->normalizer = new FileSystemPathNormalizer(
            $this->unixNormalizer,
            $this->windowsNormalizer
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->unixNormalizer, $this->normalizer->unixNormalizer());
        $this->assertSame($this->windowsNormalizer, $this->normalizer->windowsNormalizer());
    }

    public function testConstructorDefaults()
    {
        $this->normalizer = new FileSystemPathNormalizer;

        $this->assertInstanceOf(
            '\Eloquent\Pathogen\Unix\Normalizer\UnixPathNormalizer',
            $this->normalizer->unixNormalizer()
        );
        $this->assertInstanceOf(
            '\Eloquent\Pathogen\Windows\Normalizer\WindowsPathNormalizer',
            $this->normalizer->windowsNormalizer()
        );
    }

    public function testNormalizeUnix()
    {
        $path = $this->factory->create('/path/./to/foo/../bar');
        $normalizedPath = $this->normalizer->normalize($path);

        $this->assertSame('/path/to/bar', $normalizedPath->string());
        $this->assertInstanceOf(
            'Eloquent\Pathogen\AbsolutePath',
            $normalizedPath
        );
    }

    public function testNormalizeWindows()
    {
        $path = $this->factory->create('c:/path/./to/foo/../bar');
        $normalizedPath = $this->normalizer->normalize($path);

        $this->assertSame('C:/path/to/bar', $normalizedPath->string());
        $this->assertInstanceOf(
            'Eloquent\Pathogen\Windows\AbsoluteWindowsPath',
            $normalizedPath
        );
    }

    public function testInstance()
    {
        $class = Liberator::liberateClass(__NAMESPACE__ . '\FileSystemPathNormalizer');
        $class->instance = null;
        $actual = FileSystemPathNormalizer::instance();

        $this->assertInstanceOf(__NAMESPACE__ . '\FileSystemPathNormalizer', $actual);
        $this->assertSame($actual, FileSystemPathNormalizer::instance());
    }
}
