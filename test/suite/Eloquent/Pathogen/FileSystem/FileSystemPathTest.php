<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem;

use Eloquent\Pathogen\Unix\AbsoluteUnixPath;
use Eloquent\Pathogen\Unix\RelativeUnixPath;
use Eloquent\Pathogen\Windows\AbsoluteWindowsPath;
use PHPUnit_Framework_TestCase;

class FileSystemPathTest extends PHPUnit_Framework_TestCase
{
    public function createData()
    {
        //                                                                            path                       drive  atoms                             isAbsolute  hasTrailingSeparator
        return array(
            'Root'                                                           => array('/',                       null,  array(),                          true,       false),
            'Absolute'                                                       => array('/foo/bar',                null,  array('foo', 'bar'),              true,       false),
            'Absolute with trailing separator'                               => array('/foo/bar/',               null,  array('foo', 'bar'),              true,       true),
            'Absolute with empty atoms'                                      => array('/foo//bar',               null,  array('foo', 'bar'),              true,       false),
            'Absolute with empty atoms at start'                             => array('//foo',                   null,  array('foo'),                     true,       false),
            'Absolute with empty atoms at end'                               => array('/foo//',                  null,  array('foo'),                     true,       true),
            'Absolute with whitespace atoms'                                 => array('/ foo bar / baz qux ',    null,  array(' foo bar ', ' baz qux '),  true,       false),

            'Root with drive'                                                => array('C:/',                     'C',   array(),                          true,       false),
            'Root with drive and no trailing slash'                          => array('C:',                      'C',   array(),                          true,       false),
            'Absolute with drive'                                            => array('C:/foo/bar',              'C',   array('foo', 'bar'),              true,       false),
            'Absolute with trailing separator with drive'                    => array('C:/foo/bar/',             'C',   array('foo', 'bar'),              true,       true),
            'Absolute with empty atoms with drive'                           => array('C:/foo//bar',             'C',   array('foo', 'bar'),              true,       false),
            'Absolute with empty atoms at start with drive'                  => array('C://foo',                 'C',   array('foo'),                     true,       false),
            'Absolute with empty atoms at end with drive'                    => array('C:/foo//',                'C',   array('foo'),                     true,       true),
            'Absolute with whitespace atoms with drive'                      => array('C:/ foo bar / baz qux ',  'C',   array(' foo bar ', ' baz qux '),  true,       false),
            'Absolute with trailing separator with drive using backslashes'  => array('C:\\foo\\bar\\',          'C',   array('foo', 'bar'),              true,       true),

            'Empty'                                                          => array('',                        null,  array('.'),                       false,      false),
            'Self'                                                           => array('.',                       null,  array('.'),                       false,      false),
            'Relative'                                                       => array('foo/bar',                 null,  array('foo', 'bar'),              false,      false),
            'Relative with trailing separator'                               => array('foo/bar/',                null,  array('foo', 'bar'),              false,      true),
            'Relative with empty atoms'                                      => array('foo//bar',                null,  array('foo', 'bar'),              false,      false),
            'Relative with empty atoms at end'                               => array('foo/bar//',               null,  array('foo', 'bar'),              false,      true),
            'Relative with whitespace atoms'                                 => array(' foo bar / baz qux ',     null,  array(' foo bar ', ' baz qux '),  false,      false),

            'Absolute backslashes not interpreted as separators'             => array('\\foo\\bar\\',            null,  array('\\foo\\bar\\'),            false,      false),
            'Relative backslashes not interpreted as separators'             => array('foo\\bar\\',              null,  array('foo\\bar\\'),              false,      false),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testFromString($pathString, $drive, array $atoms, $isAbsolute, $hasTrailingSeparator)
    {
        $path = FileSystemPath::fromString($pathString);

        $this->assertSame($atoms, $path->atoms());
        if (null === $drive) {
            $this->assertSame($isAbsolute, $path instanceof AbsoluteUnixPath);
        } else {
            $this->assertSame($isAbsolute, $path instanceof AbsoluteWindowsPath);
            $this->assertSame($drive, $path->drive());
        }
        $this->assertSame($isAbsolute, !$path instanceof RelativeUnixPath);
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    /**
     * @dataProvider createData
     */
    public function testCreateFromAtoms($pathString, $drive, array $atoms, $isAbsolute, $hasTrailingSeparator)
    {
        $path = FileSystemPath::fromAtoms($atoms, $isAbsolute, $hasTrailingSeparator);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($isAbsolute, $path instanceof AbsoluteUnixPath);
        $this->assertSame($isAbsolute, !$path instanceof RelativeUnixPath);
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    public function testCreateFromAtomsDefaults()
    {
        $path = FileSystemPath::fromAtoms(array());

        $this->assertTrue($path instanceof AbsoluteUnixPath);
        $this->assertFalse($path->hasTrailingSeparator());
    }
}
