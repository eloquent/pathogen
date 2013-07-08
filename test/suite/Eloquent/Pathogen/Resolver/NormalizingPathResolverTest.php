<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Resolver;

use Eloquent\Pathogen\Factory\PathFactory;
use Eloquent\Pathogen\Normalizer\PathNormalizer;
use PHPUnit_Framework_TestCase;

class NormalizingPathResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new PathFactory;

        $this->normalizer = new PathNormalizer;
        $this->innerResolver = new PathResolver;
        $this->resolver = new NormalizingPathResolver(
            $this->normalizer,
            $this->innerResolver
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->normalizer, $this->resolver->normalizer());
        $this->assertSame($this->innerResolver, $this->resolver->resolver());
    }

    public function testConstructorDefaults()
    {
        $this->resolver = new NormalizingPathResolver;

        $this->assertInstanceOf(
            'Eloquent\Pathogen\Normalizer\PathNormalizer',
            $this->resolver->normalizer()
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\PathResolver',
            $this->resolver->resolver()
        );
    }

    public function testResolve()
    {
        $basePath = $this->factory->create('/foo/./bar');
        $path = $this->factory->create('baz/../qux');
        $resolvedPath = $this->resolver->resolve($basePath, $path);

        $this->assertSame('/foo/bar/qux', $resolvedPath->string());
    }
}
