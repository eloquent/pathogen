<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows;

use PHPUnit_Framework_TestCase;

class WindowsPathTest extends PHPUnit_Framework_TestCase
{
    public function createData()
    {
        //                                                                          path                      atoms                            drive isAbsolute isAnchored hasTrailingSeparator
        return array(
            'Root'                                                         => array('C:/',                    array(),                         'C',  true,      false,     false),
            'Root without slash'                                           => array('C:',                     array(),                         'C',  true,      false,     false),
            'Absolute'                                                     => array('C:/foo/bar',             array('foo', 'bar'),             'C',  true,      false,     false),
            'Absolute with trailing separator'                             => array('C:/foo/bar/',            array('foo', 'bar'),             'C',  true,      false,     true),
            'Absolute with empty atoms'                                    => array('C:/foo//bar',            array('foo', 'bar'),             'C',  true,      false,     false),
            'Absolute with empty atoms at start'                           => array('C://foo',                array('foo'),                    'C',  true,      false,     false),
            'Absolute with empty atoms at end'                             => array('C:/foo//',               array('foo'),                    'C',  true,      false,     true),
            'Absolute with whitespace atoms'                               => array('C:/ foo bar / baz qux ', array(' foo bar ', ' baz qux '), 'C',  true,      false,     false),

            'Anchored relative root'                                       => array('/',                      array(),                         null, false,     true,      false),
            'Anchored relative'                                            => array('/foo/bar',               array('foo', 'bar'),             null, false,     true,      false),
            'Anchored relative with trailing separator'                    => array('/foo/bar/',              array('foo', 'bar'),             null, false,     true,      true),
            'Anchored relative with empty atoms'                           => array('/foo//bar',              array('foo', 'bar'),             null, false,     true,      false),
            'Anchored relative with empty atoms at start'                  => array('//foo',                  array('foo'),                    null, false,     true,      false),
            'Anchored relative with empty atoms at end'                    => array('/foo//',                 array('foo'),                    null, false,     true,      true),
            'Anchored relative with whitespace atoms'                      => array('/ foo bar / baz qux ',   array(' foo bar ', ' baz qux '), null, false,     true,      false),

            'Empty'                                                        => array('',                       array('.'),                      null, false,     false,     false),
            'Self'                                                         => array('.',                      array('.'),                      null, false,     false,     false),
            'Relative'                                                     => array('foo/bar',                array('foo', 'bar'),             null, false,     false,     false),
            'Relative with trailing separator'                             => array('foo/bar/',               array('foo', 'bar'),             null, false,     false,     true),
            'Relative with empty atoms'                                    => array('foo//bar',               array('foo', 'bar'),             null, false,     false,     false),
            'Relative with empty atoms at end'                             => array('foo/bar//',              array('foo', 'bar'),             null, false,     false,     true),
            'Relative with whitespace atoms'                               => array(' foo bar / baz qux ',    array(' foo bar ', ' baz qux '), null, false,     false,     false),

            'Self with drive'                                              => array('C:.',                    array('.'),                      'C',  false,     false,     false),
            'Relative with drive'                                          => array('C:foo/bar',              array('foo', 'bar'),             'C',  false,     false,     false),
            'Relative with trailing separator with drive'                  => array('C:foo/bar/',             array('foo', 'bar'),             'C',  false,     false,     true),
            'Relative with empty atoms with drive'                         => array('C:foo//bar',             array('foo', 'bar'),             'C',  false,     false,     false),
            'Relative with empty atoms at end with drive'                  => array('C:foo/bar//',            array('foo', 'bar'),             'C',  false,     false,     true),
            'Relative with whitespace atoms with drive'                    => array('C: foo bar / baz qux ',  array(' foo bar ', ' baz qux '), 'C',  false,     false,     false),

            'Root with backslashes'                                        => array('C:\\',                   array(),                         'C',  true,      false,     false),
            'Root without slash with backslashes'                          => array('C:',                     array(),                         'C',  true,      false,     false),
            'Absolute with backslashes'                                    => array('C:\foo\bar',             array('foo', 'bar'),             'C',  true,      false,     false),
            'Absolute with trailing separator with backslashes'            => array('C:\foo\bar\\',           array('foo', 'bar'),             'C',  true,      false,     true),
            'Absolute with empty atoms with backslashes'                   => array('C:\foo\\\\bar',          array('foo', 'bar'),             'C',  true,      false,     false),
            'Absolute with empty atoms at start with backslashes'          => array('C:\\\\foo',              array('foo'),                    'C',  true,      false,     false),
            'Absolute with empty atoms at end with backslashes'            => array('C:\foo\\\\',             array('foo'),                    'C',  true,      false,     true),
            'Absolute with whitespace atoms with backslashes'              => array('C:\ foo bar \ baz qux ', array(' foo bar ', ' baz qux '), 'C',  true,      false,     false),

            'Anchored relative root with backslashes'                      => array('\\',                     array(),                         null, false,     true,      false),
            'Anchored relative with backslashes'                           => array('\foo\bar',               array('foo', 'bar'),             null, false,     true,      false),
            'Anchored relative with trailing separator with backslashes'   => array('\foo\bar\\',             array('foo', 'bar'),             null, false,     true,      true),
            'Anchored relative with empty atoms with backslashes'          => array('\foo\\\\bar',            array('foo', 'bar'),             null, false,     true,      false),
            'Anchored relative with empty atoms at start with backslashes' => array('\\\\foo',                array('foo'),                    null, false,     true,      false),
            'Anchored relative with empty atoms at end with backslashes'   => array('\foo\\\\',               array('foo'),                    null, false,     true,      true),
            'Anchored relative with whitespace atoms with backslashes'     => array('\ foo bar \ baz qux ',   array(' foo bar ', ' baz qux '), null, false,     true,      false),

            'Empty with backslashes'                                       => array('',                       array('.'),                      null, false,     false,     false),
            'Self with backslashes'                                        => array('.',                      array('.'),                      null, false,     false,     false),
            'Relative with backslashes'                                    => array('foo\bar',                array('foo', 'bar'),             null, false,     false,     false),
            'Relative with trailing separator with backslashes'            => array('foo\bar\\',              array('foo', 'bar'),             null, false,     false,     true),
            'Relative with empty atoms with backslashes'                   => array('foo\\\\bar',             array('foo', 'bar'),             null, false,     false,     false),
            'Relative with empty atoms at end with backslashes'            => array('foo\bar\\\\',            array('foo', 'bar'),             null, false,     false,     true),
            'Relative with whitespace atoms with backslashes'              => array(' foo bar \ baz qux ',    array(' foo bar ', ' baz qux '), null, false,     false,     false),

            'Self with drive with backslashes'                             => array('C:.',                    array('.'),                      'C',  false,     false,     false),
            'Relative with drive with backslashes'                         => array('C:foo\bar',              array('foo', 'bar'),             'C',  false,     false,     false),
            'Relative with trailing separator with drive with backslashes' => array('C:foo\bar\\',            array('foo', 'bar'),             'C',  false,     false,     true),
            'Relative with empty atoms with drive with backslashes'        => array('C:foo\\\\bar',           array('foo', 'bar'),             'C',  false,     false,     false),
            'Relative with empty atoms at end with drive with backslashes' => array('C:foo\bar\\\\',          array('foo', 'bar'),             'C',  false,     false,     true),
            'Relative with whitespace atoms with drive with backslashes'   => array('C: foo bar \ baz qux ',  array(' foo bar ', ' baz qux '), 'C',  false,     false,     false),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testCreate($pathString, array $atoms, $drive, $isAbsolute, $isAnchored, $hasTrailingSeparator)
    {
        $path = WindowsPath::fromString($pathString);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($isAbsolute, $path instanceof AbsoluteWindowsPath);
        $this->assertSame($isAbsolute, !$path instanceof RelativeWindowsPath);
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    public function testCreateFromAtoms()
    {
        $path = WindowsPath::fromAtoms(array('foo', 'bar'), false, true);

        $this->assertSame(array('foo', 'bar'), $path->atoms());
        $this->assertTrue($path instanceof RelativeWindowsPath);
        $this->assertTrue($path->hasTrailingSeparator());
    }

    public function testCreateFromAtomsDefaults()
    {
        $path = WindowsPath::fromAtoms(array('foo', 'bar'));

        $this->assertSame(array('foo', 'bar'), $path->atoms());
        $this->assertTrue($path instanceof RelativeWindowsPath);
        $this->assertFalse($path->hasTrailingSeparator());
    }

    /**
     * @dataProvider createData
     */
    public function testCreateFromDriveAndAtoms(
        $pathString,
        array $atoms,
        $drive,
        $isAbsolute,
        $isAnchored,
        $hasTrailingSeparator
    ) {
        $path = WindowsPath::fromDriveAndAtoms(
            $atoms,
            $drive,
            $isAbsolute,
            $isAnchored,
            $hasTrailingSeparator
        );

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($isAbsolute, $path instanceof AbsoluteWindowsPath);
        $this->assertSame($isAbsolute, !$path instanceof RelativeWindowsPath);
        if ($isAbsolute) {
            $this->assertSame($drive, $path->drive());
        } else {
            $this->assertSame($isAnchored, $path->isAnchored());
        }
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    public function testCreateFromDriveAndAtomsDefaults()
    {
        $path = WindowsPath::fromDriveAndAtoms(array('.'));

        $this->assertSame(array('.'), $path->atoms());
        $this->assertTrue($path instanceof RelativeWindowsPath);
        $this->assertFalse($path->isAnchored());
        $this->assertFalse($path->hasTrailingSeparator());
    }

    public function testCreateFromDriveAndAtomsFailureAnchoredAbsolute()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\InvalidPathStateException',
            "Invalid path state. Absolute Windows paths cannot be anchored."
        );
        WindowsPath::fromDriveAndAtoms(array(), 'C', true, true);
    }
}
