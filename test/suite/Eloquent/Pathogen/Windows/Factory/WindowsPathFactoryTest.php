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

use Eloquent\Liberator\Liberator;
use Eloquent\Pathogen\Windows\AbsoluteWindowsPath;
use Eloquent\Pathogen\Windows\RelativeWindowsPath;
use PHPUnit_Framework_TestCase;

class WindowsPathFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new WindowsPathFactory;
    }

    public function createData()
    {
        //                                                          path                      atoms                            drive isAbsolute isAnchored hasTrailingSeparator
        return array(
            'Root'                                         => array('C:/',                    array(),                         'C',  true,      false,     false),
            'Root without slash'                           => array('C:',                     array(),                         'C',  true,      false,     false),
            'Absolute'                                     => array('C:/foo/bar',             array('foo', 'bar'),             'C',  true,      false,     false),
            'Absolute with trailing separator'             => array('C:/foo/bar/',            array('foo', 'bar'),             'C',  true,      false,     true),
            'Absolute with empty atoms'                    => array('C:/foo//bar',            array('foo', 'bar'),             'C',  true,      false,     false),
            'Absolute with empty atoms at start'           => array('C://foo',                array('foo'),                    'C',  true,      false,     false),
            'Absolute with empty atoms at end'             => array('C:/foo//',               array('foo'),                    'C',  true,      false,     true),
            'Absolute with whitespace atoms'               => array('C:/ foo bar / baz qux ', array(' foo bar ', ' baz qux '), 'C',  true,      false,     false),

            'Anchored relative root'                       => array('/',                      array(),                         null, false,     true,      false),
            'Anchored relative'                            => array('/foo/bar',               array('foo', 'bar'),             null, false,     true,      false),
            'Anchored relative with trailing separator'    => array('/foo/bar/',              array('foo', 'bar'),             null, false,     true,      true),
            'Anchored relative with empty atoms'           => array('/foo//bar',              array('foo', 'bar'),             null, false,     true,      false),
            'Anchored relative with empty atoms at start'  => array('//foo',                  array('foo'),                    null, false,     true,      false),
            'Anchored relative with empty atoms at end'    => array('/foo//',                 array('foo'),                    null, false,     true,      true),
            'Anchored relative with whitespace atoms'      => array('/ foo bar / baz qux ',   array(' foo bar ', ' baz qux '), null, false,     true,      false),

            'Empty'                                        => array('',                       array('.'),                      null, false,     false,     false),
            'Self'                                         => array('.',                      array('.'),                      null, false,     false,     false),
            'Relative'                                     => array('foo/bar',                array('foo', 'bar'),             null, false,     false,     false),
            'Relative with trailing separator'             => array('foo/bar/',               array('foo', 'bar'),             null, false,     false,     true),
            'Relative with empty atoms'                    => array('foo//bar',               array('foo', 'bar'),             null, false,     false,     false),
            'Relative with empty atoms at end'             => array('foo/bar//',              array('foo', 'bar'),             null, false,     false,     true),
            'Relative with whitespace atoms'               => array(' foo bar / baz qux ',    array(' foo bar ', ' baz qux '), null, false,     false,     false),

            'Self with drive'                              => array('C:.',                    array('.'),                      'C',  false,     false,     false),
            'Relative with drive'                          => array('C:foo/bar',              array('foo', 'bar'),             'C',  false,     false,     false),
            'Relative with trailing separator with drive'  => array('C:foo/bar/',             array('foo', 'bar'),             'C',  false,     false,     true),
            'Relative with empty atoms with drive'         => array('C:foo//bar',             array('foo', 'bar'),             'C',  false,     false,     false),
            'Relative with empty atoms at end with drive'  => array('C:foo/bar//',            array('foo', 'bar'),             'C',  false,     false,     true),
            'Relative with whitespace atoms with drive'    => array('C: foo bar / baz qux ',  array(' foo bar ', ' baz qux '), 'C',  false,     false,     false),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testCreate($pathString, array $atoms, $drive, $isAbsolute, $isAnchored, $hasTrailingSeparator)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($isAbsolute, $path instanceof AbsoluteWindowsPath);
        $this->assertSame($isAbsolute, !$path instanceof RelativeWindowsPath);
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    // /**
    //  * @dataProvider createData
    //  */
    // public function testCreateFromAtoms($pathString, array $atoms, $drive, $isAbsolute, $isAnchored, $hasTrailingSeparator)
    // {
    //     $path = $this->factory->createFromAtoms($atoms, $isAbsolute, $hasTrailingSeparator);

    //     $this->assertSame($atoms, $path->atoms());
    //     $this->assertSame($isAbsolute, $path instanceof AbsolutePathInterface);
    //     $this->assertSame($isAbsolute, !$path instanceof RelativePathInterface);
    //     $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    // }

    // public function testCreateFromAtomsDefaults()
    // {
    //     $path = $this->factory->createFromAtoms(array());

    //     $this->assertTrue($path instanceof AbsolutePathInterface);
    //     $this->assertFalse($path->hasTrailingSeparator());
    // }

    // public function createData()
    // {
    //     //                                                                            path                       drive  atoms                             isAbsolute  hasTrailingSeparator
    //     return array(
    //         'Root'                                                           => array('/',                       null,  array(),                          true,       false),
    //         'Absolute'                                                       => array('/foo/bar',                null,  array('foo', 'bar'),              true,       false),
    //         'Absolute with trailing separator'                               => array('/foo/bar/',               null,  array('foo', 'bar'),              true,       true),
    //         'Absolute with empty atoms'                                      => array('/foo//bar',               null,  array('foo', 'bar'),              true,       false),
    //         'Absolute with empty atoms at start'                             => array('//foo',                   null,  array('foo'),                     true,       false),
    //         'Absolute with empty atoms at end'                               => array('/foo//',                  null,  array('foo'),                     true,       true),
    //         'Absolute with whitespace atoms'                                 => array('/ foo bar / baz qux ',    null,  array(' foo bar ', ' baz qux '),  true,       false),
    //         'Absolute with trailing separator using backslashes'             => array('\\foo\\bar\\',            null,  array('foo', 'bar'),              true,       true),

    //         'Root with drive'                                                => array('C:/',                     'C',   array(),                          true,       false),
    //         'Root with drive and no trailing slash'                          => array('C:',                      'C',   array(),                          true,       false),
    //         'Absolute with drive'                                            => array('C:/foo/bar',              'C',   array('foo', 'bar'),              true,       false),
    //         'Absolute with trailing separator with drive'                    => array('C:/foo/bar/',             'C',   array('foo', 'bar'),              true,       true),
    //         'Absolute with empty atoms with drive'                           => array('C:/foo//bar',             'C',   array('foo', 'bar'),              true,       false),
    //         'Absolute with empty atoms at start with drive'                  => array('C://foo',                 'C',   array('foo'),                     true,       false),
    //         'Absolute with empty atoms at end with drive'                    => array('C:/foo//',                'C',   array('foo'),                     true,       true),
    //         'Absolute with whitespace atoms with drive'                      => array('C:/ foo bar / baz qux ',  'C',   array(' foo bar ', ' baz qux '),  true,       false),
    //         'Absolute with trailing separator with drive using backslashes'  => array('C:\\foo\\bar\\',          'C',   array('foo', 'bar'),              true,       true),

    //         'Empty'                                                          => array('',                        null,  array('.'),                       false,      false),
    //         'Self'                                                           => array('.',                       null,  array('.'),                       false,      false),
    //         'Relative'                                                       => array('foo/bar',                 null,  array('foo', 'bar'),              false,      false),
    //         'Relative with trailing separator'                               => array('foo/bar/',                null,  array('foo', 'bar'),              false,      true),
    //         'Relative with empty atoms'                                      => array('foo//bar',                null,  array('foo', 'bar'),              false,      false),
    //         'Relative with empty atoms at end'                               => array('foo/bar//',               null,  array('foo', 'bar'),              false,      true),
    //         'Relative with whitespace atoms'                                 => array(' foo bar / baz qux ',     null,  array(' foo bar ', ' baz qux '),  false,      false),
    //         'Relative with trailing separator using backslashes'             => array('foo\\bar\\',              null,  array('foo', 'bar'),              false,      true),
    //     );
    // }

    // /**
    //  * @dataProvider createData
    //  */
    // public function testCreate($pathString, $drive, array $atoms, $isAbsolute, $hasTrailingSeparator)
    // {
    //     $path = $this->factory->create($pathString);

    //     $this->assertSame($atoms, $path->atoms());
    //     $this->assertSame($isAbsolute, $path instanceof AbsoluteWindowsPath);
    //     $this->assertSame($isAbsolute, !$path instanceof RelativeWindowsPath);
    //     if ($isAbsolute) {
    //         $this->assertSame($drive, $path->drive());
    //     }
    //     $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    // }

    // /**
    //  * @dataProvider createData
    //  */
    // public function testCreateFromAtoms($pathString, $drive, array $atoms, $isAbsolute, $hasTrailingSeparator)
    // {
    //     $path = $this->factory->createFromAtoms($atoms, $isAbsolute, $hasTrailingSeparator);

    //     $this->assertSame($atoms, $path->atoms());
    //     $this->assertSame($isAbsolute, $path instanceof AbsoluteWindowsPath);
    //     $this->assertSame($isAbsolute, !$path instanceof RelativeWindowsPath);
    //     if ($isAbsolute) {
    //         $this->assertSame('X', $path->drive());
    //     }
    //     $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    // }

    // public function testCreateFromAtomsDefaults()
    // {
    //     $path = $this->factory->createFromAtoms(array());

    //     $this->assertTrue($path instanceof AbsoluteWindowsPath);
    //     $this->assertFalse($path->hasTrailingSeparator());
    // }

    // /**
    //  * @dataProvider createData
    //  */
    // public function testCreateFromDriveAndAtoms($pathString, $drive, array $atoms, $isAbsolute, $hasTrailingSeparator)
    // {
    //     $path = $this->factory->createFromDriveAndAtoms($atoms, $drive, $isAbsolute, $hasTrailingSeparator);

    //     $this->assertSame($atoms, $path->atoms());
    //     $this->assertSame($isAbsolute, $path instanceof AbsoluteWindowsPath);
    //     $this->assertSame($isAbsolute, !$path instanceof RelativeWindowsPath);
    //     if ($isAbsolute) {
    //         $this->assertSame($drive, $path->drive());
    //     }
    //     $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    // }

    // public function testCreateFromDriveAndAtomsFailureRelativeWithDrive()
    // {
    //     $this->setExpectedException(
    //         'Eloquent\Pathogen\Exception\InvalidPathStateException',
    //         "Path cannot be relative and have a drive specifier."
    //     );
    //     $this->factory->createFromDriveAndAtoms(array(), 'C', false);
    // }

    // public function testInstance()
    // {
    //     $class = Liberator::liberateClass(__NAMESPACE__ . '\WindowsPathFactory');
    //     $class->instance = null;
    //     $actual = WindowsPathFactory::instance();

    //     $this->assertInstanceOf(__NAMESPACE__ . '\WindowsPathFactory', $actual);
    //     $this->assertSame($actual, WindowsPathFactory::instance());
    // }
}
