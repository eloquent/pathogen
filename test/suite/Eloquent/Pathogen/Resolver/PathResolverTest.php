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

use PHPUnit_Framework_TestCase;
use Eloquent\Pathogen\Factory\PathFactory;

class PathResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->resolver = PathResolver::get();
        $this->factory = PathFactory::get();
    }

    public function testGet()
    {
        $resolver = PathResolver::get();
        $this->assertInstanceOf(__NAMESPACE__ . "\PathResolver", $resolver);
    }

    public function testInstall()
    {
        PathResolver::install($this->resolver);
        $this->assertInstanceOf(__NAMESPACE__ . "\PathResolver", PathResolver::get());
        $this->assertSame($this->resolver, PathResolver::get());
    }

    public function testUninstall()
    {
        PathResolver::install($this->resolver);
        PathResolver::uninstall();
        $this->assertInstanceOf(__NAMESPACE__ . "\PathResolver", PathResolver::get());
        $this->assertNotSame($this->resolver, PathResolver::get());
    }

    public function resolveAbsolutePathData()
    {
        //                                                    basePath             path             expectedResult
        return array(
            'Root against single atom'                => array('/',                '/foo',          '/foo'),
            'Single atom against single atom'         => array('/foo',             '/bar',          '/bar'),
            'Multiple atoms against single atom'      => array('/foo/bar',         '/baz',          '/baz'),
            'Multiple atoms against multiple atoms'   => array('/foo/../../bar',   '/baz/../qux',   '/baz/../qux'),
        );
    }

    /**
     * @dataProvider resolveAbsolutePathData
     */
    public function testResolveAbsolutePaths($basePathString, $pathString, $expectedResult)
    {
        $basePath = $this->factory->create($basePathString);
        $path = $this->factory->create($pathString);
        $resolved = $this->resolver->resolve($basePath, $path);

        $this->assertSame($expectedResult, $resolved->string());
    }

    public function resolveRelativePathData()
    {
        //                                                                                        basePath      path         expectedResult
        return array(
            'Root against single atom'                                                   => array('/',          'foo',       '/foo'),
            'Single atom against single atom'                                            => array('/foo',       'bar',       '/bar'),
            'Multiple atoms against single atom'                                         => array('/foo/bar',   'baz',       '/foo/baz'),
            'Multiple atoms with slash against single atoms'                             => array('/foo/bar/',  'baz',       '/foo/baz'),
            'Multiple atoms against multiple atoms'                                      => array('/foo/bar',   'baz/qux',   '/foo/baz/qux'),
            'Multiple atoms with slash against multiple atoms'                           => array('/foo/bar/',  'baz/qux',   '/foo/baz/qux'),
            'Multiple atoms with slash against multiple atoms with slash'                => array('/foo/bar/',  'baz/qux/',  '/foo/baz/qux'),
            'Root against parent atom'                                                   => array('/',          '..',        '/..'),
            'Single atom against parent atom'                                            => array('/foo',       '..',        '/..'),
            'Single atom with slash against parent atom'                                 => array('/foo/',      '..',        '/..'),
            'Single atom with slash against parent atom with slash'                      => array('/foo/',      '../',       '/..'),
            'Multiple atoms against parent and single atom'                              => array('/foo/bar',   '../baz',    '/foo/../baz'),
            'Multiple atoms with slash against parent atom and single atom'              => array('/foo/bar/',  '../baz',    '/foo/../baz'),
            'Multiple atoms with slash against parent atom and single atom with slash'   => array('/foo/bar/',  '../baz/',   '/foo/../baz'),
        );
    }

    /**
     * @dataProvider resolveRelativePathData
     */
    public function testResolveRelativePaths($basePathString, $pathString, $expectedResult)
    {
        $basePath = $this->factory->create($basePathString);
        $path = $this->factory->create($pathString);
        $resolved = $this->resolver->resolve($basePath, $path);

        $this->assertSame($expectedResult, $resolved->string());
    }
}
