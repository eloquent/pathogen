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
        //                                                    basePath            path            expectedResult
        return array(
            'Atom, atom, slash'                      => array('/',                '/foo',         '/foo'),
            'Atom'                                   => array('/foo',             '/bar',         '/bar'),
            'Atom, atom'                             => array('/foo/bar',         '/baz',         '/baz'),
            'Atom, atom, atom, parent, parent, atom' => array('/foo/../../bar',   '/baz/../qux',  '/baz/../qux'),
        );
    }

    /**
     * @dataProvider resolveAbsolutePathData
     */
    public function testResolveAbsolutePaths($pathString, $path, $expectedResult)
    {
        $basePath = $this->factory->create($pathString);
        $resolved = $basePath->resolver()->resolve($basePath, $this->factory->create($path));

        $this->assertSame($expectedResult, $resolved->string());
    }

    public function resolveRelativePathData()
    {
        //                                                    pathString                expectedResult
        return array(
            'Atom'                                        => array('foo',                   'foo'),
            'Atom, atom'                                  => array('foo/bar',               'foo/bar'),
            'Atom, atom, atom, parent, parent, atom'      => array('foo/bar/baz/../../qux', 'foo/qux'),
            'Atom, atom, parent'                          => array('foo/bar/..',            'foo'),
            'Atom, atom, slash'                           => array('foo/bar/',              'foo/bar'),
            'Atom, parent'                                => array('foo/..',                '.'),
            'Atom, parent, atom'                          => array('foo/../bar',            'bar'),
            'Atom, parent, atom, slash'                   => array('foo/../bar/',           'bar'),
            'Atom, self, atom'                            => array('foo/./bar',             'foo/bar'),
            'Atom, self, atom, parent, atom'              => array('foo/./bar/../baz',      'foo/baz'),
            'Parent'                                      => array('..',                    '..'),
            'Parent, atom'                                => array('../foo',                '../foo'),
            'Parent, atom, parent'                        => array('../foo/..',             '..'),
            'Parent, atom, parent, atom, self'            => array('../foo/../bar/.',       '../bar'),
            'Parent, atom, parent, parent'                => array('../foo/../..',          '../..'),
            'Parent, atom, parent, parent, parent'        => array('../foo/../../..',       '../../..'),
            'Parent, atom, parent, self, atom'            => array('../foo/.././bar',       '../bar'),
            'Parent, atom, self'                          => array('../foo/.',              '../foo'),
            'Parent, atom, self, parent, atom'            => array('../foo/./../bar',       '../bar'),
            'Parent, parent'                              => array('../..',                 '../..'),
            'Parent, parent, atom, atom'                  => array('../../foo/bar',         '../../foo/bar'),
            'Parent, parent, atom, parent, atom'          => array('../../foo/../bar',      '../../bar'),
            'Parent, parent, self, parent, self'          => array('../.././../.',          '../../..'),
            'Parent, self, atom, parent, atom'            => array('.././foo/../bar',       '../bar'),
            'Parent, self, parent'                        => array('.././..',               '../..'),
            'Root'                                        => array('',                      '.'),
            'Self'                                        => array('.',                     '.'),
            'Self, atom, parent'                          => array('./foo/..',              '.'),
            'Self, parent'                                => array('./..',                  '..'),
            'Self, parent, atom'                          => array('./../foo',              '../foo'),
            'Self, self'                                  => array('./.',                   '.'),
            'Self, self, atom'                            => array('././foo',               'foo'),
        );
    }

    /**
     * @dataProvider resolveRelativePathData
     */
    public function testResolveRelativePaths($pathString, $expectedResult)
    {
        $path = $this->factory->create($pathString);
        $resolved = $this->resolver->resolve($path);

        $this->assertSame($expectedResult, $resolved->string());
    }
}
