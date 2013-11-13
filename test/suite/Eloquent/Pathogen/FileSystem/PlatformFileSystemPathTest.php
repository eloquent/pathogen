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

use Eloquent\Liberator\Liberator;
use Eloquent\Pathogen\Unix\AbsoluteUnixPath;
use Eloquent\Pathogen\Unix\RelativeUnixPath;
use Eloquent\Pathogen\Windows\AbsoluteWindowsPath;
use Eloquent\Pathogen\Windows\RelativeWindowsPath;
use Icecave\Isolator\Isolator;
use PHPUnit_Framework_TestCase;
use Phake;

class PlatformFileSystemPathTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->isolator = Phake::mock(Isolator::className());
        $this->factory = Phake::partialMock(
            __NAMESPACE__ . '\Factory\PlatformFileSystemPathFactory',
            null,
            null,
            $this->isolator
        );
        Liberator::liberateClass(__NAMESPACE__ . '\Factory\PlatformFileSystemPathFactory')->instance = $this->factory;

        Phake::when($this->isolator)->defined('PHP_WINDOWS_VERSION_BUILD')->thenReturn(false);
    }

    protected function tearDown()
    {
        Liberator::liberateClass(__NAMESPACE__ . '\Factory\PlatformFileSystemPathFactory')->instance = null;

        parent::tearDown();
    }

    public function createData()
    {
        //                                                                            path                       atoms                                  isAbsolute  hasTrailingSeparator
        return array(
            'Root'                                                           => array('/',                       array(),                               true,       false),
            'Absolute'                                                       => array('/foo/bar',                array('foo', 'bar'),                   true,       false),
            'Absolute with trailing separator'                               => array('/foo/bar/',               array('foo', 'bar'),                   true,       true),
            'Absolute with empty atoms'                                      => array('/foo//bar',               array('foo', 'bar'),                   true,       false),
            'Absolute with empty atoms at start'                             => array('//foo',                   array('foo'),                          true,       false),
            'Absolute with empty atoms at end'                               => array('/foo//',                  array('foo'),                          true,       true),
            'Absolute with whitespace atoms'                                 => array('/ foo bar / baz qux ',    array(' foo bar ', ' baz qux '),       true,       false),

            'Root with drive'                                                => array('C:/',                     array('C:'),                           false,      true),
            'Root with drive and no trailing slash'                          => array('C:',                      array('C:'),                           false,      false),
            'Absolute with drive'                                            => array('C:/foo/bar',              array('C:', 'foo', 'bar'),             false,      false),
            'Absolute with trailing separator with drive'                    => array('C:/foo/bar/',             array('C:', 'foo', 'bar'),             false,      true),
            'Absolute with empty atoms with drive'                           => array('C:/foo//bar',             array('C:', 'foo', 'bar'),             false,      false),
            'Absolute with empty atoms at start with drive'                  => array('C://foo',                 array('C:', 'foo'),                    false,      false),
            'Absolute with empty atoms at end with drive'                    => array('C:/foo//',                array('C:', 'foo'),                    false,      true),
            'Absolute with whitespace atoms with drive'                      => array('C:/ foo bar / baz qux ',  array('C:', ' foo bar ', ' baz qux '), false,      false),
            'Absolute with trailing separator with drive using backslashes'  => array('C:\foo\bar\\',            array('C:\foo\bar\\'),                 false,      false),

            'Empty'                                                          => array('',                        array('.'),                            false,      false),
            'Self'                                                           => array('.',                       array('.'),                            false,      false),
            'Relative'                                                       => array('foo/bar',                 array('foo', 'bar'),                   false,      false),
            'Relative with trailing separator'                               => array('foo/bar/',                array('foo', 'bar'),                   false,      true),
            'Relative with empty atoms'                                      => array('foo//bar',                array('foo', 'bar'),                   false,      false),
            'Relative with empty atoms at end'                               => array('foo/bar//',               array('foo', 'bar'),                   false,      true),
            'Relative with whitespace atoms'                                 => array(' foo bar / baz qux ',     array(' foo bar ', ' baz qux '),       false,      false),

            'Self with drive'                                                => array('C:.',                     array('C:.'),                          false,      false),
            'Relative with drive'                                            => array('C:foo/bar',               array('C:foo', 'bar'),                 false,      false),
            'Relative with trailing separator with drive'                    => array('C:foo/bar/',              array('C:foo', 'bar'),                 false,      true),
            'Relative with empty atoms with drive'                           => array('C:foo//bar',              array('C:foo', 'bar'),                 false,      false),
            'Relative with empty atoms at end with drive'                    => array('C:foo/bar//',             array('C:foo', 'bar'),                 false,      true),
            'Relative with whitespace atoms with drive'                      => array('C: foo bar / baz qux ',   array('C: foo bar ', ' baz qux '),     false,      false),

            'Anchored backslashes not interpreted as separators'             => array('\foo\bar\\',              array('\foo\bar\\'),                   false,      false),
            'Relative backslashes not interpreted as separators'             => array('foo\bar\\',               array('foo\bar\\'),                    false,      false),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testFromString($pathString, array $atoms, $isAbsolute, $hasTrailingSeparator)
    {
        $path = PlatformFileSystemPath::fromString($pathString);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($isAbsolute, $path instanceof AbsoluteUnixPath);
        $this->assertSame($isAbsolute, !$path instanceof RelativeUnixPath);
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    /**
     * @dataProvider createData
     */
    public function testCreateFromAtoms($pathString, array $atoms, $isAbsolute, $hasTrailingSeparator)
    {
        $path = PlatformFileSystemPath::fromAtoms($atoms, $isAbsolute, $hasTrailingSeparator);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($isAbsolute, $path instanceof AbsoluteUnixPath);
        $this->assertSame($isAbsolute, !$path instanceof RelativeUnixPath);
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    public function createDataWindows()
    {
        //                                                                            path                       drive  atoms                             isAbsolute  hasTrailingSeparator
        return array(
            'Root with drive'                                                => array('C:/',                     'C',   array(),                          true,       false),
            'Root with drive and no trailing slash'                          => array('C:',                      'C',   array(),                          true,       false),
            'Absolute with drive'                                            => array('C:/foo/bar',              'C',   array('foo', 'bar'),              true,       false),
            'Absolute with trailing separator with drive'                    => array('C:/foo/bar/',             'C',   array('foo', 'bar'),              true,       true),
            'Absolute with empty atoms with drive'                           => array('C:/foo//bar',             'C',   array('foo', 'bar'),              true,       false),
            'Absolute with empty atoms at start with drive'                  => array('C://foo',                 'C',   array('foo'),                     true,       false),
            'Absolute with empty atoms at end with drive'                    => array('C:/foo//',                'C',   array('foo'),                     true,       true),
            'Absolute with whitespace atoms with drive'                      => array('C:/ foo bar / baz qux ',  'C',   array(' foo bar ', ' baz qux '),  true,       false),
            'Absolute with trailing separator with drive using backslashes'  => array('C:\foo\bar\\',            'C',   array('foo', 'bar'),              true,       true),

            'Anchored root'                                                  => array('/',                       null,  array(),                          false,      false),
            'Anchored'                                                       => array('/foo/bar',                null,  array('foo', 'bar'),              false,      false),
            'Anchored with trailing separator'                               => array('/foo/bar/',               null,  array('foo', 'bar'),              false,      true),
            'Anchored with empty atoms'                                      => array('/foo//bar',               null,  array('foo', 'bar'),              false,      false),
            'Anchored with empty atoms at start'                             => array('//foo',                   null,  array('foo'),                     false,      false),
            'Anchored with empty atoms at end'                               => array('/foo//',                  null,  array('foo'),                     false,      true),
            'Anchored with whitespace atoms'                                 => array('/ foo bar / baz qux ',    null,  array(' foo bar ', ' baz qux '),  false,      false),
            'Anchored with trailing separator using backslashes'             => array('\foo\bar\\',              null,  array('foo', 'bar'),              false,      true),

            'Empty'                                                          => array('',                        null,  array('.'),                       false,      false),
            'Self'                                                           => array('.',                       null,  array('.'),                       false,      false),
            'Relative'                                                       => array('foo/bar',                 null,  array('foo', 'bar'),              false,      false),
            'Relative with trailing separator'                               => array('foo/bar/',                null,  array('foo', 'bar'),              false,      true),
            'Relative with empty atoms'                                      => array('foo//bar',                null,  array('foo', 'bar'),              false,      false),
            'Relative with empty atoms at end'                               => array('foo/bar//',               null,  array('foo', 'bar'),              false,      true),
            'Relative with whitespace atoms'                                 => array(' foo bar / baz qux ',     null,  array(' foo bar ', ' baz qux '),  false,      false),
            'Relative with trailing separator using backslashes'             => array('foo\bar\\',               null,  array('foo', 'bar'),              false,      true),
        );
    }

    /**
     * @dataProvider createDataWindows
     */
    public function testFromStringWindows($pathString, $drive, array $atoms, $isAbsolute, $hasTrailingSeparator)
    {
        Phake::when($this->isolator)->defined('PHP_WINDOWS_VERSION_BUILD')->thenReturn(true);
        $path = PlatformFileSystemPath::fromString($pathString);

        $this->assertSame($atoms, $path->atoms());
        if ($isAbsolute) {
            $this->assertTrue($path instanceof AbsoluteWindowsPath);
            $this->assertSame($drive, $path->drive());
        } else {
            $this->assertTrue($path instanceof RelativeWindowsPath);
        }
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    /**
     * @dataProvider createDataWindows
     */
    public function testCreateFromAtomsWindows($pathString, $drive, array $atoms, $isAbsolute, $hasTrailingSeparator)
    {
        if ($isAbsolute || count($atoms) < 1) {
            // silently skip this row
            $this->assertTrue(true);

            return;
        }
        Phake::when($this->isolator)->defined('PHP_WINDOWS_VERSION_BUILD')->thenReturn(true);
        $path = PlatformFileSystemPath::fromAtoms($atoms, $isAbsolute, $hasTrailingSeparator);

        $this->assertSame($atoms, $path->atoms());
        if ($isAbsolute) {
            $this->assertTrue($path instanceof AbsoluteWindowsPath);
            $this->assertNull($path->drive());
        } else {
            $this->assertTrue($path instanceof RelativeWindowsPath);
        }
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }
}
