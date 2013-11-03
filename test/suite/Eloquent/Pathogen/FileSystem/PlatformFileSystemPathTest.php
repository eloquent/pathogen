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
        $this->factory = new Factory\PlatformFileSystemPathFactory(null, null, $this->isolator);
        Liberator::liberateClass(__NAMESPACE__ . '\Factory\PlatformFileSystemPathFactory')->instance = $this->factory;

        Phake::when($this->isolator)->defined('PHP_WINDOWS_VERSION_BUILD')->thenReturn(true);
    }

    protected function tearDown()
    {
        Liberator::liberateClass(__NAMESPACE__ . '\Factory\PlatformFileSystemPathFactory')->instance = null;

        parent::tearDown();
    }

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
            'Absolute with trailing separator using backslashes'             => array('\\foo\\bar\\',            null,  array('foo', 'bar'),              true,       true),

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
            'Relative with trailing separator using backslashes'             => array('foo\\bar\\',              null,  array('foo', 'bar'),              false,      true),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testFromString($pathString, $drive, array $atoms, $isAbsolute, $hasTrailingSeparator)
    {
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
     * @dataProvider createData
     */
    public function testCreateFromAtoms($pathString, $drive, array $atoms, $isAbsolute, $hasTrailingSeparator)
    {
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
