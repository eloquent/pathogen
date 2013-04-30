<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen;

use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Pathogen\RelativePath
 * @covers \Eloquent\Pathogen\AbstractPath
 */
class RelativePathTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new Factory\PathFactory;
    }

    // tests for PathInterface implementation ==================================

    public function testRootPathConstructor()
    {
        $path = $this->factory->create('');

        $this->assertSame(array('.'), $path->atoms());
        $this->assertSame(false, $path->hasTrailingSeparator());
        $this->assertSame('.', $path->string());
        $this->assertSame('.', strval($path->string()));
    }

    public function pathData()
    {
        //                             path                     atoms                              hasTrailingSeparator
        return array(
            'Single atom'     => array('foo',                  array('foo'),                      false),
            'Trailing slash'  => array('foo/',                 array('foo'),                      true),
            'Multiple atoms'  => array('foo/bar',              array('foo', 'bar'),               false),
            'Parent atom'     => array('foo/../bar',           array('foo', '..', 'bar'),         false),
            'Self atom'       => array('foo/./bar',            array('foo', '.', 'bar'),          false),
            'Whitespace'      => array(' foo bar / baz qux ',  array(' foo bar ', ' baz qux '),   false),
        );
    }

    /**
     * @dataProvider pathData
     */
    public function testConstructor($pathString, array $atoms, $hasTrailingSeparator)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
        $this->assertSame($pathString, $path->string());
        $this->assertSame($pathString, strval($path->string()));
    }
}
