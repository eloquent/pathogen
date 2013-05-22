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
        $this->resolver = new BoundPathResolver($this->factory->create('/foo/bar'));
    }

    public function resolveAbsolutePathData()
    {
        //                             path             expectedResult
        return array(
            'Single atom'     => array('/baz',          '/baz'),
            'Multiple atoms'  => array('/baz/../qux',   '/baz/../qux'),
        );
    }

    /**
     * @dataProvider resolveAbsolutePathData
     */
    public function testResolveAbsolutePaths($pathString, $expectedResult)
    {
        $path = $this->factory->create($pathString);
        $resolved = $this->resolver->resolve($path);

        $this->assertSame($expectedResult, $resolved->string());
    }

    public function resolveRelativePathData()
    {
        //                                                      path         expectedResult
        return array(
            'Single atom'                              => array('baz',       '/foo/bar/baz'),
            'Multiple atoms'                           => array('baz/qux',   '/foo/bar/baz/qux'),
            'Multiple atoms with slash'                => array('baz/qux/',  '/foo/bar/baz/qux'),
            'Parent atom'                              => array('..',        '/foo/bar/..'),
            'Parent atom with slash'                   => array('../',       '/foo/bar/..'),
            'Parent and single atom'                   => array('../baz',    '/foo/bar/../baz'),
            'Parent atom and single atom with slash'   => array('../baz/',   '/foo/bar/../baz'),
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
