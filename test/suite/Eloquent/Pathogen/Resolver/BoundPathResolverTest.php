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
use PHPUnit_Framework_TestCase;

class BoundPathResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new PathFactory;

        $this->basePath = $this->factory->create('/foo/bar');
        $this->innerResolver = new PathResolver;
        $this->resolver = new BoundPathResolver(
            $this->basePath,
            $this->innerResolver
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->basePath, $this->resolver->basePath());
        $this->assertSame($this->innerResolver, $this->resolver->resolver());
    }

    public function testConstructorDefaults()
    {
        $this->resolver = new BoundPathResolver($this->basePath);

        $this->assertInstanceOf(
            __NAMESPACE__ . '\PathResolver',
            $this->resolver->resolver()
        );
    }

    public function testResolve()
    {
        $path = $this->factory->create('baz/qux');
        $resolvedPath = $this->resolver->resolve($path);

        $this->assertSame('/foo/bar/baz/qux', $resolvedPath->string());
    }
}
