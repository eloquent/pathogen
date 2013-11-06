<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Factory;

use Eloquent\Liberator\Liberator;
use Eloquent\Pathogen\AbsolutePath;
use Eloquent\Pathogen\RelativePath;
use PHPUnit_Framework_TestCase;

class PathFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new PathFactory;
    }

    public function createData()
    {
        //                                                 path                     atoms                             isAbsolute  hasTrailingSeparator
        return array(
            'Root'                                => array('/',                     array(),                          true,       false),
            'Absolute'                            => array('/foo/bar',              array('foo', 'bar'),              true,       false),
            'Absolute with trailing separator'    => array('/foo/bar/',             array('foo', 'bar'),              true,       true),
            'Absolute with empty atoms'           => array('/foo//bar',             array('foo', 'bar'),              true,       false),
            'Absolute with empty atoms at start'  => array('//foo',                 array('foo'),                     true,       false),
            'Absolute with empty atoms at end'    => array('/foo//',                array('foo'),                     true,       true),
            'Absolute with whitespace atoms'      => array('/ foo bar / baz qux ',  array(' foo bar ', ' baz qux '),  true,       false),

            'Empty'                               => array('',                      array('.'),                       false,      false),
            'Self'                                => array('.',                     array('.'),                       false,      false),
            'Relative'                            => array('foo/bar',               array('foo', 'bar'),              false,      false),
            'Relative with trailing separator'    => array('foo/bar/',              array('foo', 'bar'),              false,      true),
            'Relative with empty atoms'           => array('foo//bar',              array('foo', 'bar'),              false,      false),
            'Relative with empty atoms at end'    => array('foo/bar//',             array('foo', 'bar'),              false,      true),
            'Relative with whitespace atoms'      => array(' foo bar / baz qux ',   array(' foo bar ', ' baz qux '),  false,      false),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testCreate($pathString, array $atoms, $isAbsolute, $hasTrailingSeparator)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($isAbsolute, $path instanceof AbsolutePath);
        $this->assertSame($isAbsolute, !$path instanceof RelativePath);
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    /**
     * @dataProvider createData
     */
    public function testCreateFromAtoms($pathString, array $atoms, $isAbsolute, $hasTrailingSeparator)
    {
        $path = $this->factory->createFromAtoms($atoms, $isAbsolute, $hasTrailingSeparator);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($isAbsolute, $path instanceof AbsolutePath);
        $this->assertSame($isAbsolute, !$path instanceof RelativePath);
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    public function testCreateFromAtomsDefaults()
    {
        $path = $this->factory->createFromAtoms(array());

        $this->assertTrue($path instanceof AbsolutePath);
        $this->assertFalse($path->hasTrailingSeparator());
    }

    public function testInstance()
    {
        $class = Liberator::liberateClass(__NAMESPACE__ . '\PathFactory');
        $class->instance = null;
        $actual = PathFactory::instance();

        $this->assertInstanceOf(__NAMESPACE__ . '\PathFactory', $actual);
        $this->assertSame($actual, PathFactory::instance());
    }
}
