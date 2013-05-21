<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows\Factory;

use Eloquent\Pathogen\RelativePathInterface;
use Eloquent\Pathogen\Windows\AbsoluteWindowsPathInterface;
use PHPUnit_Framework_TestCase;

class WindowsPathFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new WindowsPathFactory('X');
    }

    public function testConstructor()
    {
        $this->assertSame('X', $this->factory->defaultDrive());
    }

    public function testConstructorDefaults()
    {
        $this->factory = new WindowsPathFactory;

        $this->assertNull($this->factory->defaultDrive());
    }

    public function createData()
    {
        //                                                           path                       drive  atoms                             isAbsolute  hasTrailingSeparator
        return array(
            'Root'                                          => array('/',                       null,  array(),                          true,       false),
            'Absolute'                                      => array('/foo/bar',                null,  array('foo', 'bar'),              true,       false),
            'Absolute with trailing separator'              => array('/foo/bar/',               null,  array('foo', 'bar'),              true,       true),
            'Absolute with empty atoms'                     => array('/foo//bar',               null,  array('foo', 'bar'),              true,       false),
            'Absolute with empty atoms at start'            => array('//foo',                   null,  array('foo'),                     true,       false),
            'Absolute with empty atoms at end'              => array('/foo//',                  null,  array('foo'),                     true,       true),
            'Absolute with whitespace atoms'                => array('/ foo bar / baz qux ',    null,  array(' foo bar ', ' baz qux '),  true,       false),

            'Root with drive'                               => array('C:/',                     'C',   array(),                          true,       false),
            'Root with drive and no trailing slash'         => array('C:',                      'C',   array(),                          true,       false),
            'Absolute with drive'                           => array('C:/foo/bar',              'C',   array('foo', 'bar'),              true,       false),
            'Absolute with trailing separator with drive'   => array('C:/foo/bar/',             'C',   array('foo', 'bar'),              true,       true),
            'Absolute with empty atoms with drive'          => array('C:/foo//bar',             'C',   array('foo', 'bar'),              true,       false),
            'Absolute with empty atoms at start with drive' => array('C://foo',                 'C',   array('foo'),                     true,       false),
            'Absolute with empty atoms at end with drive'   => array('C:/foo//',                'C',   array('foo'),                     true,       true),
            'Absolute with whitespace atoms with drive'     => array('C:/ foo bar / baz qux ',  'C',   array(' foo bar ', ' baz qux '),  true,       false),

            'Empty'                                         => array('',                        null,  array(),                          false,      false),
            'Self'                                          => array('.',                       null,  array('.'),                       false,      false),
            'Relative'                                      => array('foo/bar',                 null,  array('foo', 'bar'),              false,      false),
            'Relative with trailing separator'              => array('foo/bar/',                null,  array('foo', 'bar'),              false,      true),
            'Relative with empty atoms'                     => array('foo//bar',                null,  array('foo', 'bar'),              false,      false),
            'Relative with empty atoms at end'              => array('foo/bar//',               null,  array('foo', 'bar'),              false,      true),
            'Relative with whitespace atoms'                => array(' foo bar / baz qux ',     null,  array(' foo bar ', ' baz qux '),  false,      false),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testCreate($pathString, $drive, array $atoms, $isAbsolute, $hasTrailingSeparator)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($isAbsolute, $path instanceof AbsoluteWindowsPathInterface);
        $this->assertSame($isAbsolute, !$path instanceof RelativePathInterface);
        if ($isAbsolute) {
            $this->assertSame($drive, $path->drive());
        }
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    /**
     * @dataProvider createData
     */
    public function testCreateFromAtoms($pathString, $drive, array $atoms, $isAbsolute, $hasTrailingSeparator)
    {
        $path = $this->factory->createFromAtoms($atoms, $isAbsolute, $hasTrailingSeparator);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($isAbsolute, $path instanceof AbsoluteWindowsPathInterface);
        $this->assertSame($isAbsolute, !$path instanceof RelativePathInterface);
        if ($isAbsolute) {
            $this->assertSame('X', $path->drive());
        }
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    /**
     * @dataProvider createData
     */
    public function testCreateFromDriveAndAtoms($pathString, $drive, array $atoms, $isAbsolute, $hasTrailingSeparator)
    {
        $path = $this->factory->createFromDriveAndAtoms($atoms, $drive, $isAbsolute, $hasTrailingSeparator);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($isAbsolute, $path instanceof AbsoluteWindowsPathInterface);
        $this->assertSame($isAbsolute, !$path instanceof RelativePathInterface);
        if ($isAbsolute) {
            $this->assertSame($drive, $path->drive());
        }
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    public function testCreateFromDriveAndAtomsFailureRelativeWithDrive()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\InvalidPathStateException',
            "Path cannot be relative and have a drive specifier."
        );
        $this->factory->createFromDriveAndAtoms(array(), 'C', false);
    }
}
