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

use ArrayIterator;
use Eloquent\Liberator\Liberator;
use Eloquent\Pathogen\Factory\PathFactory;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Pathogen\Windows\RelativeWindowsPath
 * @covers \Eloquent\Pathogen\RelativePath
 * @covers \Eloquent\Pathogen\AbstractPath
 */
class RelativeWindowsPathTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new Factory\WindowsPathFactory;
        $this->regularPathFactory = new PathFactory;
    }

    public function pathData()
    {
        //                                        path                     atoms                             drive expectedPathString       hasTrailingSeparator
        return array(
            'Self'                       => array('.',                    array('.'),                        null, '.',                     false),
            'Single atom'                => array('foo',                  array('foo'),                      null, 'foo',                   false),
            'Trailing slash'             => array('foo/',                 array('foo'),                      null, 'foo/',                  true),
            'Multiple atoms'             => array('foo/bar',              array('foo', 'bar'),               null, 'foo/bar',               false),
            'Parent atom'                => array('foo/../bar',           array('foo', '..', 'bar'),         null, 'foo/../bar',            false),
            'Self atom'                  => array('foo/./bar',            array('foo', '.', 'bar'),          null, 'foo/./bar',             false),
            'Whitespace'                 => array(' foo bar / baz qux ',  array(' foo bar ', ' baz qux '),   null, ' foo bar / baz qux ',   false),

            'Self with drive'            => array('C:.',                    array('.'),                      'C',  'C:.',                   false),
            'Single atom with drive'     => array('C:foo',                  array('foo'),                    'C',  'C:foo',                 false),
            'Trailing slash with drive'  => array('C:foo/',                 array('foo'),                    'C',  'C:foo/',                true),
            'Multiple atoms with drive'  => array('C:foo/bar',              array('foo', 'bar'),             'C',  'C:foo/bar',             false),
            'Parent atom with drive'     => array('C:foo/../bar',           array('foo', '..', 'bar'),       'C',  'C:foo/../bar',          false),
            'Self atom with drive'       => array('C:foo/./bar',            array('foo', '.', 'bar'),        'C',  'C:foo/./bar',           false),
            'Whitespace with drive'      => array('C: foo bar / baz qux ',  array(' foo bar ', ' baz qux '), 'C',  'C: foo bar / baz qux ', false),
        );
    }

    /**
     * @dataProvider pathData
     */
    public function testConstructor($pathString, array $atoms, $drive, $expectedPathString, $hasTrailingSeparator)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($drive, $path->drive());
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
        $this->assertSame($expectedPathString, $path->string());
        $this->assertSame($expectedPathString, strval($path));
    }

    public function testConstructorDefaults()
    {
        $this->path = new RelativeWindowsPath(array('.'));

        $this->assertFalse($this->path->hasTrailingSeparator());
    }

    public function testConstructorFailureAtomContainingSeparator()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo/bar'. Path atoms must not contain separators."
        );
        new RelativeWindowsPath(array('foo/bar'));
    }

    public function testConstructorFailureAtomContainingBackslash()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo\\\\bar'. Path atoms must not contain separators."
        );
        new RelativeWindowsPath(array('foo\bar'));
    }

    public function invalidPathAtomCharacterData()
    {
        $characters = array_merge(
            range(chr(0), chr(31)),
            array('<', '>', ':', '"', '|', '?', '*')
        );

        $data = array();
        foreach ($characters as $character) {
            $name = sprintf(
                'Invalid path atom character (ASCII %d)',
                ord($character)
            );
            $data[$name] = array($character);
        }

        return $data;
    }

    /**
     * @dataProvider invalidPathAtomCharacterData
     */
    public function testConstructorFailureAtomContainingInvalidCharacter($character)
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\InvalidPathAtomCharacterException',
            sprintf(
                'Invalid path atom %s. Path atom contains invalid character %s.',
                var_export(sprintf('foo%sbar', $character), true),
                var_export($character, true)
            )
        );
        new RelativeWindowsPath(array(sprintf('foo%sbar', $character)));
    }

    public function testConstructorFailureEmptyAtom()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\EmptyPathAtomException'
        );
        new RelativeWindowsPath(array(''));
    }

    public function testConstructorFailureEmptyPath()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\EmptyPathException'
        );
        new RelativeWindowsPath(array());
    }

    public function testConstructorFailureInvalidDriveSpecifierCharacter()
    {
        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\InvalidDriveSpecifierException'
        );
        new RelativeWindowsPath(array('foo'), '$');
    }

    public function testConstructorFailureInvalidDriveSpecifierEmpty()
    {
        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\InvalidDriveSpecifierException'
        );
        new RelativeWindowsPath(array('foo'), '');
    }

    public function testConstructorFailureInvalidDriveSpecifierLength()
    {
        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\InvalidDriveSpecifierException'
        );
        new RelativeWindowsPath(array('foo'), 'CC');
    }

    // Implementation of WindowsPathInterface ==================================

    public function testMatchesDrive()
    {
        $withDrive = $this->factory->create('C:foo/bar');
        $noDrive = $this->factory->create('foo/bar');

        $this->assertTrue($withDrive->matchesDrive('C'));
        $this->assertTrue($withDrive->matchesDrive('c'));
        $this->assertFalse($withDrive->matchesDrive('X'));
        $this->assertFalse($withDrive->matchesDrive(null));

        $this->assertFalse($noDrive->matchesDrive('C'));
        $this->assertFalse($noDrive->matchesDrive('c'));
        $this->assertFalse($noDrive->matchesDrive('X'));
        $this->assertTrue($noDrive->matchesDrive(null));
    }

    public function testMatchesDriveOrNull()
    {
        $withDrive = $this->factory->create('C:foo/bar');
        $noDrive = $this->factory->create('foo/bar');

        $this->assertTrue($withDrive->matchesDriveOrNull('C'));
        $this->assertTrue($withDrive->matchesDriveOrNull('c'));
        $this->assertFalse($withDrive->matchesDriveOrNull('X'));
        $this->assertTrue($withDrive->matchesDriveOrNull(null));

        $this->assertTrue($noDrive->matchesDriveOrNull('C'));
        $this->assertTrue($noDrive->matchesDriveOrNull('c'));
        $this->assertTrue($noDrive->matchesDriveOrNull('X'));
        $this->assertTrue($noDrive->matchesDriveOrNull(null));
    }

    public function testJoinDrive()
    {
        $path = $this->factory->create('C:foo/bar');
        $joinedToDrive = $path->joinDrive('X');
        $joinedToNull = $path->joinDrive(null);

        $anchoredPath = $this->factory->create('/foo/bar');
        $anchoredJoinedToDrive = $anchoredPath->joinDrive('X');
        $anchoredJoinedToNull = $anchoredPath->joinDrive(null);

        $this->assertTrue($joinedToDrive instanceof RelativeWindowsPath);
        $this->assertSame('X:foo/bar', $joinedToDrive->string());
        $this->assertTrue($joinedToNull instanceof RelativeWindowsPath);
        $this->assertSame('foo/bar', $joinedToNull->string());

        $this->assertTrue($anchoredJoinedToDrive instanceof AbsoluteWindowsPath);
        $this->assertSame('X:/foo/bar', $anchoredJoinedToDrive->string());
        $this->assertTrue($anchoredJoinedToNull instanceof RelativeWindowsPath);
        $this->assertSame('/foo/bar', $anchoredJoinedToNull->string());
    }

    // tests for PathInterface implementation ==================================

    public function testAtomAt()
    {
        $path = $this->factory->create('foo/bar');

        $this->assertSame('foo', $path->atomAt(0));
        $this->assertSame('bar', $path->atomAt(1));
        $this->assertSame('bar', $path->atomAt(-1));
        $this->assertSame('foo', $path->atomAt(-2));
    }

    public function testAtomAtWithDrive()
    {
        $path = $this->factory->create('C:foo/bar');

        $this->assertSame('foo', $path->atomAt(0));
        $this->assertSame('bar', $path->atomAt(1));
        $this->assertSame('bar', $path->atomAt(-1));
        $this->assertSame('foo', $path->atomAt(-2));
    }

    public function testAtomAtAnchored()
    {
        $path = $this->factory->create('/foo/bar');

        $this->assertSame('foo', $path->atomAt(0));
        $this->assertSame('bar', $path->atomAt(1));
        $this->assertSame('bar', $path->atomAt(-1));
        $this->assertSame('foo', $path->atomAt(-2));
    }

    public function testAtomAtFailure()
    {
        $path = $this->factory->create('foo/bar');

        $this->setExpectedException('Eloquent\Pathogen\Exception\UndefinedAtomException');
        $path->atomAt(2);
    }

    public function testAtomAtDefault()
    {
        $path = $this->factory->create('foo/bar');

        $this->assertSame('foo', $path->atomAtDefault(0, 'baz'));
        $this->assertSame('bar', $path->atomAtDefault(1, 'baz'));
        $this->assertSame('baz', $path->atomAtDefault(2, 'baz'));
        $this->assertSame('bar', $path->atomAtDefault(-1, 'baz'));
        $this->assertSame('foo', $path->atomAtDefault(-2, 'baz'));
        $this->assertSame('baz', $path->atomAtDefault(-3, 'baz'));
        $this->assertNull($path->atomAtDefault(2));
    }

    public function testAtomAtDefaultWithDrive()
    {
        $path = $this->factory->create('C:foo/bar');

        $this->assertSame('foo', $path->atomAtDefault(0, 'baz'));
        $this->assertSame('bar', $path->atomAtDefault(1, 'baz'));
        $this->assertSame('baz', $path->atomAtDefault(2, 'baz'));
        $this->assertSame('bar', $path->atomAtDefault(-1, 'baz'));
        $this->assertSame('foo', $path->atomAtDefault(-2, 'baz'));
        $this->assertSame('baz', $path->atomAtDefault(-3, 'baz'));
        $this->assertNull($path->atomAtDefault(2));
    }

    public function testAtomAtDefaultAnchored()
    {
        $path = $this->factory->create('/foo/bar');

        $this->assertSame('foo', $path->atomAtDefault(0, 'baz'));
        $this->assertSame('bar', $path->atomAtDefault(1, 'baz'));
        $this->assertSame('baz', $path->atomAtDefault(2, 'baz'));
        $this->assertSame('bar', $path->atomAtDefault(-1, 'baz'));
        $this->assertSame('foo', $path->atomAtDefault(-2, 'baz'));
        $this->assertSame('baz', $path->atomAtDefault(-3, 'baz'));
        $this->assertNull($path->atomAtDefault(2));
    }

    public function sliceAtomsData()
    {
        //                                  path                index  length  expectedResult
        return array(
            'Slice till end'       => array('foo/bar/baz/qux',  1,     null,   array('bar', 'baz', 'qux')),
            'Slice specific range' => array('foo/bar/baz/qux',  1,     2,      array('bar', 'baz')),
        );
    }

    /**
     * @dataProvider sliceAtomsData
     */
    public function testSliceAtoms($pathString, $index, $length, array $expectedResult)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($expectedResult, $path->sliceAtoms($index, $length));
    }

    public function namePartData()
    {
        //                                             path            name            nameWithoutExtension  namePrefix  nameSuffix  extension
        return array(
            'Self'                            => array('.',            '.',            '',                   '',         '',         ''),
            'No extensions'                   => array('foo',          'foo',          'foo',                'foo',      null,       null),
            'Empty extension'                 => array('foo.',         'foo.',         'foo',                'foo',      '',         ''),
            'Whitespace extension'            => array('foo. ',        'foo. ',        'foo',                'foo',      ' ',        ' '),
            'Single extension'                => array('foo.bar',      'foo.bar',      'foo',                'foo',      'bar',      'bar'),
            'Multiple extensions'             => array('foo.bar.baz',  'foo.bar.baz',  'foo.bar',            'foo',      'bar.baz',  'baz'),
            'No name with single extension'   => array('.foo',         '.foo',         '',                   '',         'foo',      'foo'),
            'No name with multiple extension' => array('.foo.bar',     '.foo.bar',     '.foo',               '',         'foo.bar',  'bar'),
        );
    }

    /**
     * @dataProvider namePartData
     */
    public function testNamePartMethods($pathString, $name, $nameWithoutExtension, $namePrefix, $nameSuffix, $extension)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($name, $path->name());
        $this->assertSame($nameWithoutExtension, $path->nameWithoutExtension());
        $this->assertSame($namePrefix, $path->namePrefix());
        $this->assertSame($nameSuffix, $path->nameSuffix());
        $this->assertSame($extension, $path->extension());
        $this->assertSame(null !== $extension, $path->hasExtension());
    }

    public function nameAtomsData()
    {
        //                                  path        nameAtoms
        return array(
            'Self'                 => array('.',        array('', '')),
            'Single name atom'     => array('foo',      array('foo')),
            'Multiple name atoms'  => array('foo.bar',  array('foo', 'bar')),
            'Multiple path atoms'  => array('foo/bar',  array('bar')),
        );
    }

    /**
     * @dataProvider nameAtomsData
     */
    public function testNameAtoms($pathString, array $nameAtoms)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($nameAtoms, $path->nameAtoms());
    }

    public function testNameAtomAt()
    {
        $path = $this->factory->create('foo.bar');

        $this->assertSame('foo', $path->nameAtomAt(0));
        $this->assertSame('bar', $path->nameAtomAt(1));
        $this->assertSame('bar', $path->nameAtomAt(-1));
        $this->assertSame('foo', $path->nameAtomAt(-2));
    }

    public function testNameAtomAtFailure()
    {
        $path = $this->factory->create('foo.bar');

        $this->setExpectedException('Eloquent\Pathogen\Exception\UndefinedAtomException');
        $path->nameAtomAt(2);
    }

    public function testNameAtomAtDefault()
    {
        $path = $this->factory->create('foo.bar');

        $this->assertSame('foo', $path->nameAtomAtDefault(0, 'baz'));
        $this->assertSame('bar', $path->nameAtomAtDefault(1, 'baz'));
        $this->assertSame('baz', $path->nameAtomAtDefault(2, 'baz'));
        $this->assertSame('bar', $path->nameAtomAtDefault(-1, 'baz'));
        $this->assertSame('foo', $path->nameAtomAtDefault(-2, 'baz'));
        $this->assertSame('baz', $path->nameAtomAtDefault(-3, 'baz'));
        $this->assertNull($path->nameAtomAtDefault(2));
    }

    public function sliceNameAtomsData()
    {
        //                                  path                index  length  expectedResult
        return array(
            'Slice till end'       => array('foo.bar.baz.qux',  1,     null,   array('bar', 'baz', 'qux')),
            'Slice specific range' => array('foo.bar.baz.qux',  1,     2,      array('bar', 'baz')),
        );
    }

    /**
     * @dataProvider sliceNameAtomsData
     */
    public function testNameSliceAtoms($pathString, $index, $length, array $expectedResult)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($expectedResult, $path->sliceNameAtoms($index, $length));
    }

    public function containsData()
    {
        //                                                  path                 needle         caseSensitive  expectedResult
        return array(
            'Self'                                 => array('.',                 '',            null,          true),
            'Prefix'                               => array('foo/bar/baz.qux',   'FOO/BAR',     null,          true),
            'Middle'                               => array('foo/bar/baz.qux',   'BAR/BAZ',     null,          true),
            'Suffix'                               => array('foo/bar/baz.qux',   '/BAZ.QUX',    null,          true),
            'Not found'                            => array('foo/bar/baz.qux',   'DOOM',        null,          false),

            'Self case sensitive'                  => array('.',                 '',            true,          true),
            'Prefix case sensitive'                => array('foo/bar/baz.qux',   'foo/bar',     true,          true),
            'Middle case sensitive'                => array('foo/bar/baz.qux',   'bar/baz',     true,          true),
            'Suffix case sensitive'                => array('foo/bar/baz.qux',   '/baz.qux',    true,          true),
            'Not found case sensitive'             => array('foo/bar/baz.qux',   'FOO',         true,          false),

            'Self with drive'                      => array('C:.',               '',            null,          true),
            'Prefix with drive'                    => array('C:foo/bar/baz.qux', 'c:FOO/BAR',   null,          true),
            'Middle with drive'                    => array('C:foo/bar/baz.qux', 'BAR/BAZ',     null,          true),
            'Suffix with drive'                    => array('C:foo/bar/baz.qux', '/BAZ.QUX',    null,          true),
            'Not found with drive'                 => array('C:foo/bar/baz.qux', 'DOOM',        null,          false),

            'Self case sensitive with drive'       => array('C:.',               '',            true,          true),
            'Prefix case sensitive with drive'     => array('C:foo/bar/baz.qux', 'C:foo/bar',   true,          true),
            'Middle case sensitive with drive'     => array('C:foo/bar/baz.qux', 'bar/baz',     true,          true),
            'Suffix case sensitive with drive'     => array('C:foo/bar/baz.qux', '/baz.qux',    true,          true),
            'Not found case sensitive with drive'  => array('C:foo/bar/baz.qux', 'FOO',         true,          false),

            'Self anchored'                        => array('/.',                '',            null,          true),
            'Prefix anchored'                      => array('/foo/bar/baz.qux',  '/FOO/BAR',    null,          true),
            'Middle anchored'                      => array('/foo/bar/baz.qux',  'BAR/BAZ',     null,          true),
            'Suffix anchored'                      => array('/foo/bar/baz.qux',  '/BAZ.QUX',    null,          true),
            'Not found anchored'                   => array('/foo/bar/baz.qux',  'DOOM',        null,          false),

            'Self case sensitive anchored'         => array('/.',                '',            true,          true),
            'Prefix case sensitive anchored'       => array('/foo/bar/baz.qux',  '/foo/bar',    true,          true),
            'Middle case sensitive anchored'       => array('/foo/bar/baz.qux',  'bar/baz',     true,          true),
            'Suffix case sensitive anchored'       => array('/foo/bar/baz.qux',  '/baz.qux',    true,          true),
            'Not found case sensitive anchored'    => array('/foo/bar/baz.qux',  'FOO',         true,          false),
        );
    }

    /**
     * @dataProvider containsData
     */
    public function testContains($pathString, $needle, $caseSensitive, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->factory->create($pathString)->contains($needle, $caseSensitive)
        );
    }

    public function startsWithData()
    {
        //                                                  path                 needle       caseSensitive  expectedResult
        return array(
            'Self'                                 => array('.',                 '',          null,          true),
            'Prefix'                               => array('foo/bar/baz.qux',   'FOO/BAR',   null,          true),
            'Middle'                               => array('foo/bar/baz.qux',   'BAR/BAZ',   null,          false),
            'Suffix'                               => array('foo/bar/baz.qux',   '/BAZ.QUX',  null,          false),
            'Not found'                            => array('foo/bar/baz.qux',   'DOOM',      null,          false),

            'Self case sensitive'                  => array('.',                 '',          true,          true),
            'Prefix case sensitive'                => array('foo/bar/baz.qux',   'foo/bar',   true,          true),
            'Middle case sensitive'                => array('foo/bar/baz.qux',   'bar/baz',   true,          false),
            'Suffix case sensitive'                => array('foo/bar/baz.qux',   '/baz.qux',  true,          false),
            'Not found case sensitive'             => array('foo/bar/baz.qux',   'FOO',       true,          false),

            'Self with drive'                      => array('C:.',               '',          null,          true),
            'Prefix with drive'                    => array('C:foo/bar/baz.qux', 'c:FOO/BAR', null,          true),
            'Middle with drive'                    => array('C:foo/bar/baz.qux', 'BAR/BAZ',   null,          false),
            'Suffix with drive'                    => array('C:foo/bar/baz.qux', '/BAZ.QUX',  null,          false),
            'Not found with drive'                 => array('C:foo/bar/baz.qux', 'DOOM',      null,          false),

            'Self case sensitive with drive'       => array('C:.',               '',          true,          true),
            'Prefix case sensitive with drive'     => array('C:foo/bar/baz.qux', 'C:foo/bar', true,          true),
            'Middle case sensitive with drive'     => array('C:foo/bar/baz.qux', 'bar/baz',   true,          false),
            'Suffix case sensitive with drive'     => array('C:foo/bar/baz.qux', '/baz.qux',  true,          false),
            'Not found case sensitive with drive'  => array('C:foo/bar/baz.qux', 'FOO',       true,          false),

            'Self anchored'                        => array('/.',                '',          null,          true),
            'Prefix anchored'                      => array('/foo/bar/baz.qux',  '/FOO/BAR',  null,          true),
            'Middle anchored'                      => array('/foo/bar/baz.qux',  'BAR/BAZ',   null,          false),
            'Suffix anchored'                      => array('/foo/bar/baz.qux',  '/BAZ.QUX',  null,          false),
            'Not found anchored'                   => array('/foo/bar/baz.qux',  'DOOM',      null,          false),

            'Self case sensitive anchored'         => array('/.',                '',          true,          true),
            'Prefix case sensitive anchored'       => array('/foo/bar/baz.qux',  '/foo/bar',  true,          true),
            'Middle case sensitive anchored'       => array('/foo/bar/baz.qux',  'bar/baz',   true,          false),
            'Suffix case sensitive anchored'       => array('/foo/bar/baz.qux',  '/baz.qux',  true,          false),
            'Not found case sensitive anchored'    => array('/foo/bar/baz.qux',  'FOO',       true,          false),
        );
    }

    /**
     * @dataProvider startsWithData
     */
    public function testStartsWith($pathString, $needle, $caseSensitive, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->factory->create($pathString)->startsWith($needle, $caseSensitive)
        );
    }

    public function endsWithData()
    {
        //                                                  path                 needle       caseSensitive  expectedResult
        return array(
            'Self'                                 => array('.',                 '',          null,          true),
            'Prefix'                               => array('foo/bar/baz.qux',   'FOO/BAR',   null,          false),
            'Middle'                               => array('foo/bar/baz.qux',   'BAR/BAZ',   null,          false),
            'Suffix'                               => array('foo/bar/baz.qux',   '/BAZ.QUX',  null,          true),
            'Not found'                            => array('foo/bar/baz.qux',   'DOOM',      null,          false),

            'Self case sensitive'                  => array('.',                 '',          true,          true),
            'Prefix case sensitive'                => array('foo/bar/baz.qux',   'foo/bar',   true,          false),
            'Middle case sensitive'                => array('foo/bar/baz.qux',   'bar/baz',   true,          false),
            'Suffix case sensitive'                => array('foo/bar/baz.qux',   '/baz.qux',  true,          true),
            'Not found case sensitive'             => array('foo/bar/baz.qux',   'FOO',       true,          false),

            'Self with drive'                      => array('C:.',               '',          null,          true),
            'Prefix with drive'                    => array('C:foo/bar/baz.qux', 'c:FOO/BAR', null,          false),
            'Middle with drive'                    => array('C:foo/bar/baz.qux', 'BAR/BAZ',   null,          false),
            'Suffix with drive'                    => array('C:foo/bar/baz.qux', '/BAZ.QUX',  null,          true),
            'Not found with drive'                 => array('C:foo/bar/baz.qux', 'DOOM',      null,          false),

            'Self case sensitive with drive'       => array('C:.',               '',          true,          true),
            'Prefix case sensitive with drive'     => array('C:foo/bar/baz.qux', 'C:foo/bar', true,          false),
            'Middle case sensitive with drive'     => array('C:foo/bar/baz.qux', 'bar/baz',   true,          false),
            'Suffix case sensitive with drive'     => array('C:foo/bar/baz.qux', '/baz.qux',  true,          true),
            'Not found case sensitive with drive'  => array('C:foo/bar/baz.qux', 'FOO',       true,          false),

            'Self anchored'                        => array('/.',                '',          null,          true),
            'Prefix anchored'                      => array('/foo/bar/baz.qux',  '/FOO/BAR',  null,          false),
            'Middle anchored'                      => array('/foo/bar/baz.qux',  'BAR/BAZ',   null,          false),
            'Suffix anchored'                      => array('/foo/bar/baz.qux',  '/BAZ.QUX',  null,          true),
            'Not found anchored'                   => array('/foo/bar/baz.qux',  'DOOM',      null,          false),

            'Self case sensitive anchored'         => array('/.',                '',          true,          true),
            'Prefix case sensitive anchored'       => array('/foo/bar/baz.qux',  '/foo/bar',  true,          false),
            'Middle case sensitive anchored'       => array('/foo/bar/baz.qux',  'bar/baz',   true,          false),
            'Suffix case sensitive anchored'       => array('/foo/bar/baz.qux',  '/baz.qux',  true,          true),
            'Not found case sensitive anchored'    => array('/foo/bar/baz.qux',  'FOO',       true,          false),
        );
    }

    /**
     * @dataProvider endsWithData
     */
    public function testEndsWith($pathString, $needle, $caseSensitive, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->factory->create($pathString)->endsWith($needle, $caseSensitive)
        );
    }

    public function matchesData()
    {
        //                                                    path                  pattern        caseSensitive  flags          expectedResult
        return array(
            'Prefix'                                 => array('foo/bar/baz.qux',    'FOO/BAR*',    null,          null,          true),
            'Middle'                                 => array('foo/bar/baz.qux',    '*BAR/BAZ*',   null,          null,          true),
            'Suffix'                                 => array('foo/bar/baz.qux',    '*/BAZ.QUX',   null,          null,          true),
            'Surrounding'                            => array('foo/bar/baz.qux',    'FOO*.QUX',    null,          null,          true),
            'Single character'                       => array('foo/bar/baz.qux',    '*B?R*',       null,          null,          true),
            'Single character no match'              => array('foo/bar/baz.qux',    '*F?X*',       null,          null,          false),
            'Set'                                    => array('foo/bar/baz.qux',    '*BA[RZ]*',    null,          null,          true),
            'Set no match'                           => array('foo/bar/baz.qux',    '*BA[X]*',     null,          null,          false),
            'Negated set'                            => array('foo/bar/baz.qux',    '*BA[!RX]*',   null,          null,          true),
            'Negated set no match'                   => array('foo/bar/baz.qux',    '*BA[!RZ]*',   null,          null,          false),
            'Range'                                  => array('foo/bar/baz.qux',    '*BA[A-R]*',   null,          null,          true),
            'Range no match'                         => array('foo/bar/baz.qux',    '*BA[S-Y]*',   null,          null,          false),
            'Negated range'                          => array('foo/bar/baz.qux',    '*BA[!S-Y]*',  null,          null,          true),
            'Negated range no match'                 => array('foo/bar/baz.qux',    '*BA[!R-Z]*',  null,          null,          false),
            'No partial match'                       => array('foo/bar/baz.qux',    'BAR',         null,          null,          false),
            'Not found'                              => array('foo/bar/baz.qux',    'DOOM',        null,          null,          false),

            'Case sensitive'                         => array('foo/bar/baz.qux',    '*bar/baz*',   true,          null,          true),
            'Case sensitive no match'                => array('foo/bar/baz.qux',    '*FOO*',       true,          null,          false),
            'Special flags'                          => array('foo/bar/baz.qux',    'FOO/BAR/*',   false,         FNM_PATHNAME,  true),
            'Special flags no match'                 => array('foo/bar/baz.qux',    '*FOO/BAR*',   false,         FNM_PATHNAME,  false),

            'Prefix with drive'                      => array('C:foo/bar/baz.qux',  'c:FOO/BAR*',  null,          null,          true),
            'Middle with drive'                      => array('C:foo/bar/baz.qux',  '*BAR/BAZ*',   null,          null,          true),
            'Suffix with drive'                      => array('C:foo/bar/baz.qux',  '*/BAZ.QUX',   null,          null,          true),
            'Surrounding with drive'                 => array('C:foo/bar/baz.qux',  'c:FOO*.QUX',  null,          null,          true),
            'Single character with drive'            => array('C:foo/bar/baz.qux',  '*B?R*',       null,          null,          true),
            'Single character no match with drive'   => array('C:foo/bar/baz.qux',  '*F?X*',       null,          null,          false),
            'Set with drive'                         => array('C:foo/bar/baz.qux',  '*BA[RZ]*',    null,          null,          true),
            'Set no match with drive'                => array('C:foo/bar/baz.qux',  '*BA[X]*',     null,          null,          false),
            'Negated set with drive'                 => array('C:foo/bar/baz.qux',  '*BA[!RX]*',   null,          null,          true),
            'Negated set no match with drive'        => array('C:foo/bar/baz.qux',  '*BA[!RZ]*',   null,          null,          false),
            'Range with drive'                       => array('C:foo/bar/baz.qux',  '*BA[A-R]*',   null,          null,          true),
            'Range no match with drive'              => array('C:foo/bar/baz.qux',  '*BA[S-Y]*',   null,          null,          false),
            'Negated range with drive'               => array('C:foo/bar/baz.qux',  '*BA[!S-Y]*',  null,          null,          true),
            'Negated range no match with drive'      => array('C:foo/bar/baz.qux',  '*BA[!R-Z]*',  null,          null,          false),
            'No partial match with drive'            => array('C:foo/bar/baz.qux',  'BAR',         null,          null,          false),
            'Not found with drive'                   => array('C:foo/bar/baz.qux',  'DOOM',        null,          null,          false),

            'Case sensitive with drive'              => array('C:foo/bar/baz.qux',  '*bar/baz*',   true,          null,          true),
            'Case sensitive no match with drive'     => array('C:foo/bar/baz.qux',  '*FOO*',       true,          null,          false),
            'Special flags with drive'               => array('C:foo/bar/baz.qux',  'c:FOO/BAR/*', false,         FNM_PATHNAME,  true),
            'Special flags no match with drive'      => array('C:foo/bar/baz.qux',  '*FOO/BAR*',   false,         FNM_PATHNAME,  false),

            'Prefix anchored'                        => array('/foo/bar/baz.qux',   '/FOO/BAR*',   null,          null,          true),
            'Middle anchored'                        => array('/foo/bar/baz.qux',   '*BAR/BAZ*',   null,          null,          true),
            'Suffix anchored'                        => array('/foo/bar/baz.qux',   '*/BAZ.QUX',   null,          null,          true),
            'Surrounding anchored'                   => array('/foo/bar/baz.qux',   '/FOO*.QUX',   null,          null,          true),
            'Single character anchored'              => array('/foo/bar/baz.qux',   '*B?R*',       null,          null,          true),
            'Single character no match anchored'     => array('/foo/bar/baz.qux',   '*F?X*',       null,          null,          false),
            'Set anchored'                           => array('/foo/bar/baz.qux',   '*BA[RZ]*',    null,          null,          true),
            'Set no match anchored'                  => array('/foo/bar/baz.qux',   '*BA[X]*',     null,          null,          false),
            'Negated set anchored'                   => array('/foo/bar/baz.qux',   '*BA[!RX]*',   null,          null,          true),
            'Negated set no match anchored'          => array('/foo/bar/baz.qux',   '*BA[!RZ]*',   null,          null,          false),
            'Range anchored'                         => array('/foo/bar/baz.qux',   '*BA[A-R]*',   null,          null,          true),
            'Range no match anchored'                => array('/foo/bar/baz.qux',   '*BA[S-Y]*',   null,          null,          false),
            'Negated range anchored'                 => array('/foo/bar/baz.qux',   '*BA[!S-Y]*',  null,          null,          true),
            'Negated range no match anchored'        => array('/foo/bar/baz.qux',   '*BA[!R-Z]*',  null,          null,          false),
            'No partial match anchored'              => array('/foo/bar/baz.qux',   'BAR',         null,          null,          false),
            'Not found anchored'                     => array('/foo/bar/baz.qux',   'DOOM',        null,          null,          false),

            'Case sensitive anchored'                => array('/foo/bar/baz.qux',   '*bar/baz*',   true,          null,          true),
            'Case sensitive no match anchored'       => array('/foo/bar/baz.qux',   '*FOO*',       true,          null,          false),
            'Special flags anchored'                 => array('/foo/bar/baz.qux',   '/FOO/BAR/*',  false,         FNM_PATHNAME,  true),
            'Special flags no match anchored'        => array('/foo/bar/baz.qux',   '*FOO/BAR*',   false,         FNM_PATHNAME,  false),
        );
    }

    /**
     * @dataProvider matchesData
     */
    public function testMatches($pathString, $pattern, $caseSensitive, $flags, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->factory->create($pathString)->matches($pattern, $caseSensitive, $flags)
        );
    }

    public function matchesRegexData()
    {
        //                                   path                  pattern              matches                                                flags                 offset  expectedResult
        return array(
            'Match'                 => array('C:foo/bar/baz.qux',  '{.*(FOO)/BAR.*}i',  array('C:foo/bar/baz.qux', 'foo'),                     null,                 null,   true),
            'No match'              => array('C:foo/bar/baz.qux',  '{.*DOOM.*}i',       array(),                                               null,                 null,   false),
            'Special flags'         => array('C:foo/bar/baz.qux',  '{.*FOO/(BAR).*}i',  array(array('C:foo/bar/baz.qux', 0), array('bar', 6)), PREG_OFFSET_CAPTURE,  null,   true),
            'Match with offset'     => array('C:foo/bar/baz.qux',  '{BAR}i',            array('bar'),                                          null,                 6,      true),
            'No match with offset'  => array('C:foo/bar/baz.qux',  '{BAR}i',            array(),                                               null,                 7,      false),
        );
    }

    /**
     * @dataProvider matchesRegexData
     */
    public function testMatchesRegex($pathString, $pattern, $matches, $flags, $offset, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->factory->create($pathString)->matchesRegex($pattern, $actualMatches, $flags, $offset)
        );
        $this->assertSame($matches, $actualMatches);
    }

    public function nameContainsData()
    {
        //                                       path                  needle      caseSensitive  expectedResult
        return array(
            'Empty'                     => array('C:.',                '',         null,          true),
            'Prefix'                    => array('C:foo/bar.baz.qux',  'BAR.BAZ',  null,          true),
            'Middle'                    => array('C:foo/bar.baz.qux',  'BAZ',      null,          true),
            'Suffix'                    => array('C:foo/bar.baz.qux',  'BAZ.QUX',  null,          true),
            'Not found'                 => array('C:foo/bar.baz.qux',  'DOOM',     null,          false),
            'Match only in name'        => array('C:foo/bar.baz.qux',  'foo',      null,          false),

            'Empty case sensitive'      => array('C:.',                '',         true,          true),
            'Prefix case sensitive'     => array('C:foo/bar.baz.qux',  'bar.baz',  true,          true),
            'Middle case sensitive'     => array('C:foo/bar.baz.qux',  'baz',      true,          true),
            'Suffix case sensitive'     => array('C:foo/bar.baz.qux',  'baz.qux',  true,          true),
            'Not found case sensitive'  => array('C:foo/bar.baz.qux',  'BAR',      true,          false),
        );
    }

    /**
     * @dataProvider nameContainsData
     */
    public function testNameContains($pathString, $needle, $caseSensitive, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->factory->create($pathString)->nameContains($needle, $caseSensitive)
        );
    }

    public function nameStartsWithData()
    {
        //                                       path                  needle      caseSensitive  expectedResult
        return array(
            'Empty'                     => array('C:.',                '',         null,          true),
            'Prefix'                    => array('C:foo/bar.baz.qux',  'BAR.BAZ',  null,          true),
            'Middle'                    => array('C:foo/bar.baz.qux',  'BAZ',      null,          false),
            'Suffix'                    => array('C:foo/bar.baz.qux',  'BAZ.QUX',  null,          false),
            'Not found'                 => array('C:foo/bar.baz.qux',  'DOOM',     null,          false),

            'Empty case sensitive'      => array('C:.',                '',         true,          true),
            'Prefix case sensitive'     => array('C:foo/bar.baz.qux',  'bar.baz',  true,          true),
            'Middle case sensitive'     => array('C:foo/bar.baz.qux',  'baz',      true,          false),
            'Suffix case sensitive'     => array('C:foo/bar.baz.qux',  'baz.qux',  true,          false),
            'Not found case sensitive'  => array('C:foo/bar.baz.qux',  'BAR',      true,          false),
        );
    }

    /**
     * @dataProvider nameStartsWithData
     */
    public function testNameStartsWith($pathString, $needle, $caseSensitive, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->factory->create($pathString)->nameStartsWith($needle, $caseSensitive)
        );
    }

    public function nameMatchesData()
    {
        //                                         path                  pattern        caseSensitive  flags        expectedResult
        return array(
            'Prefix'                      => array('C:foo/bar.baz.qux',  'BAR.BAZ*',    null,          null,        true),
            'Middle'                      => array('C:foo/bar.baz.qux',  '*BAZ*',       null,          null,        true),
            'Suffix'                      => array('C:foo/bar.baz.qux',  '*BAZ.QUX',    null,          null,        true),
            'Surrounding'                 => array('C:foo/bar.baz.qux',  'BAR.*.QUX',   null,          null,        true),
            'Single character'            => array('C:foo/bar.baz.qux',  '*B?R*',       null,          null,        true),
            'Single character no match'   => array('C:foo/bar.baz.qux',  '*B?X*',       null,          null,        false),
            'Set'                         => array('C:foo/bar.baz.qux',  '*BA[RZ]*',    null,          null,        true),
            'Set no match'                => array('C:foo/bar.baz.qux',  '*BA[X]*',     null,          null,        false),
            'Negated set'                 => array('C:foo/bar.baz.qux',  '*BA[!RX]*',   null,          null,        true),
            'Negated set no match'        => array('C:foo/bar.baz.qux',  '*BA[!RZ]*',   null,          null,        false),
            'Range'                       => array('C:foo/bar.baz.qux',  '*BA[A-R]*',   null,          null,        true),
            'Range no match'              => array('C:foo/bar.baz.qux',  '*BA[S-Y]*',   null,          null,        false),
            'Negated range'               => array('C:foo/bar.baz.qux',  '*BA[!S-Y]*',  null,          null,        true),
            'Negated range no match'      => array('C:foo/bar.baz.qux',  '*BA[!R-Z]*',  null,          null,        false),
            'No partial match'            => array('C:foo/bar.baz.qux',  'BAZ',         null,          null,        false),
            'Not found'                   => array('C:foo/bar.baz.qux',  'DOOM',        null,          null,        false),

            'Case sensitive'              => array('C:foo/bar.baz.qux',  '*baz*',       true,          null,        true),
            'Case sensitive no match'     => array('C:foo/bar.baz.qux',  '*BAZ*',       true,          null,        false),
            'Special flags'               => array('C:foo/.bar.baz',     '.bar*',       false,         FNM_PERIOD,  true),
            'Special flags no match'      => array('C:foo/.bar.baz',     '*bar*',       false,         FNM_PERIOD,  false),
        );
    }

    /**
     * @dataProvider nameMatchesData
     */
    public function testNameMatches($pathString, $pattern, $caseSensitive, $flags, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->factory->create($pathString)->nameMatches($pattern, $caseSensitive, $flags)
        );
    }

    public function nameMatchesRegexData()
    {
        //                                   path                  pattern               matches                                           flags                 offset  expectedResult
        return array(
            'Match'                 => array('C:foo/bar.baz.qux',  '{.*(BAR)\.BAZ.*}i',  array('bar.baz.qux', 'bar'),                      null,                 null,   true),
            'No match'              => array('C:foo/bar.baz.qux',  '{.*DOOM.*}i',        array(),                                          null,                 null,   false),
            'Special flags'         => array('C:foo/bar.baz.qux',  '{.*BAR\.(BAZ).*}i',  array(array('bar.baz.qux', 0), array('baz', 4)),  PREG_OFFSET_CAPTURE,  null,   true),
            'Match with offset'     => array('C:foo/bar.baz.qux',  '{BAZ}i',             array('baz'),                                     null,                 4,      true),
            'No match with offset'  => array('C:foo/bar.baz.qux',  '{BAZ}i',             array(),                                          null,                 5,      false),
        );
    }

    /**
     * @dataProvider nameMatchesRegexData
     */
    public function testNameMatchesRegex($pathString, $pattern, $matches, $flags, $offset, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->factory->create($pathString)->nameMatchesRegex($pattern, $actualMatches, $flags, $offset)
        );
        $this->assertSame($matches, $actualMatches);
    }

    public function parentData()
    {
        //                                        path          numLevels  parent
        return array(
            'Self'                       => array('.',          null,      './..'),
            'Single atom'                => array('foo',        null,      'foo/..'),
            'Multiple atoms'             => array('foo/bar',    null,      'foo/bar/..'),
            'Up one level'               => array('foo',        1,         'foo/..'),
            'Up two levels'              => array('foo',        2,         'foo/../..'),

            'Self with drive'            => array('C:.',        null,      'C:./..'),
            'Single atom with drive'     => array('C:foo',      null,      'C:foo/..'),
            'Multiple atoms with drive'  => array('C:foo/bar',  null,      'C:foo/bar/..'),
            'Up one level with drive'    => array('C:foo',      1,         'C:foo/..'),
            'Up two levels with drive'   => array('C:foo',      2,         'C:foo/../..'),

            'Self anchored'              => array('/.',         null,      '/./..'),
            'Single atom anchored'       => array('/foo',       null,      '/foo/..'),
            'Multiple atoms anchored'    => array('/foo/bar',   null,      '/foo/bar/..'),
            'Up one level anchored'      => array('/foo',       1,         '/foo/..'),
            'Up two levels anchored'     => array('/foo',       2,         '/foo/../..'),
        );
    }

    /**
     * @dataProvider parentData
     */
    public function testParent($pathString, $numLevels, $parentPathString)
    {
        $path = $this->factory->create($pathString);
        $parentPath = $path->parent($numLevels);

        $this->assertSame($parentPathString, $parentPath->string());
    }

    public function stripTrailingSlashData()
    {
        //                               path            expectedResult
        return array(
            'Single atom'       => array('C:foo/',       'C:foo'),
            'Multiple atoms'    => array('C:foo/bar/',   'C:foo/bar'),
            'Whitespace atoms'  => array('C:foo/bar /',  'C:foo/bar '),
            'No trailing slash' => array('C:foo',        'C:foo'),
            'Self'              => array('C:.',          'C:.'),
        );
    }

    /**
     * @dataProvider stripTrailingSlashData
     */
    public function testStripTrailingSlash($pathString, $expectedResult)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($expectedResult, $path->stripTrailingSlash()->string());
    }

    public function extensionStrippingData()
    {
        //                                   path              strippedExtension  strippedSuffix
        return array(
            'Self'                  => array('C:.',            'C:.',              'C:.'),
            'No extensions'         => array('C:foo',          'C:foo',            'C:foo'),
            'Empty extension'       => array('C:foo.',         'C:foo',            'C:foo'),
            'Whitespace extension'  => array('C:foo . ',       'C:foo ',           'C:foo '),
            'Single extension'      => array('C:foo.bar',      'C:foo',            'C:foo'),
            'Multiple extensions'   => array('C:foo.bar.baz',  'C:foo.bar',        'C:foo'),
        );
    }

    /**
     * @dataProvider extensionStrippingData
     */
    public function testExtensionStripping($pathString, $strippedExtensionString, $strippedSuffixString)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($strippedExtensionString, $path->stripExtension()->string());
        $this->assertSame($strippedSuffixString, $path->stripNameSuffix()->string());
    }

    public function joinAtomsData()
    {
        //                                              path           atoms                 expectedResult
        return array(
            'Single atom to self'              => array('C:.',         array('foo'),         'C:./foo'),
            'Multiple atoms to self'           => array('C:.',         array('foo', 'bar'),  'C:./foo/bar'),
            'Multiple atoms to multiple atoms' => array('C:foo/bar',   array('baz', 'qux'),  'C:foo/bar/baz/qux'),
            'Whitespace atoms'                 => array('C:foo',       array(' '),           'C:foo/ '),
            'Special atoms'                    => array('C:foo',       array('.', '..'),     'C:foo/./..'),
        );
    }

    /**
     * @dataProvider joinAtomsData
     */
    public function testJoinAtoms($pathString, array $atoms, $expectedResultString)
    {
        $path = $this->factory->create($pathString);
        $result = call_user_func_array(array($path, 'joinAtoms'), $atoms);

        $this->assertSame($expectedResultString, $result->string());
    }

    public function testJoinAtomsFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('C:foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz/qux'. Path atoms must not contain separators."
        );
        $path->joinAtoms('bar', 'baz/qux');
    }

    public function testJoinAtomsFailureEmptyAtom()
    {
        $path = $this->factory->create('C:foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\EmptyPathAtomException'
        );
        $path->joinAtoms('bar', '');
    }

    /**
     * @dataProvider joinAtomsData
     */
    public function testJoinAtomSequence($pathString, array $atoms, $expectedResultString)
    {
        $path = $this->factory->create($pathString);
        $result = $path->joinAtomSequence($atoms);

        $this->assertSame($expectedResultString, $result->string());
    }

    public function testJoinAtomSequenWithNonArray()
    {
        $path = $this->factory->create('C:foo');
        $result = $path->joinAtomSequence(new ArrayIterator(array('bar', 'baz')));

        $this->assertSame('C:foo/bar/baz', $result->string());
    }

    public function testJoinAtomSequenceFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('C:foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz/qux'. Path atoms must not contain separators."
        );
        $path->joinAtomSequence(array('bar', 'baz/qux'));
    }

    public function testJoinAtomSequenceFailureEmptyAtom()
    {
        $path = $this->factory->create('C:foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\EmptyPathAtomException'
        );
        $path->joinAtomSequence(array('bar', ''));
    }

    public function joinData()
    {
        //                                              path          joinPath    expectedResult
        return array(
            'Relative atom to self'            => array('C:.',        './foo',     'C:././foo'),
            'Single atom to self'              => array('C:.',        'foo',       'C:./foo'),
            'Multiple atoms to self'           => array('C:.',        './foo/bar', 'C:././foo/bar'),
            'Multiple atoms to multiple atoms' => array('C:foo/bar',  'baz/qux',   'C:foo/bar/baz/qux'),
            'Whitespace atoms'                 => array('C:foo',      ' ',         'C:foo/ '),
            'Special atoms'                    => array('C:foo',      './..',      'C:foo/./..'),

            'Anchored'                         => array('C:foo/bar',  '/baz/qux',  'C:/baz/qux'),
        );
    }

    /**
     * @dataProvider joinData
     */
    public function testJoin($pathString, $joinPathString, $expectedResultString)
    {
        $path = $this->factory->create($pathString);
        $joinPath = $this->factory->create($joinPathString);
        $result = $path->join($joinPath);

        $this->assertSame($expectedResultString, $result->string());
    }

    public function testJoinFailureAbsoluteJoinPath()
    {
        $path = $this->factory->create('C:foo');
        $joinPath = $this->factory->create('C:/bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $path->join($joinPath);
    }

    public function testJoinFailureDriveMismatch()
    {
        $path = $this->factory->create('C:foo');
        $joinPath = $this->factory->create('X:bar');

        $this->setExpectedException(__NAMESPACE__ . '\Exception\DriveMismatchException');
        $path->join($joinPath);
    }

    public function joinTrailingSlashData()
    {
        //                                     path         expectedResult
        return array(
            'Self atom'               => array('C:.',       'C:./'),
            'Single atom'             => array('C:foo',     'C:foo/'),
            'Whitespace atom'         => array('C:foo ',    'C:foo /'),
            'Multiple atoms'          => array('C:foo/bar', 'C:foo/bar/'),
            'Existing trailing slash' => array('C:foo/',    'C:foo/'),
        );
    }

    /**
     * @dataProvider joinTrailingSlashData
     */
    public function testJoinTrailingSlash($pathString, $expectedResult)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($expectedResult, $path->joinTrailingSlash()->string());
    }

    public function joinExtensionsData()
    {
        //                                                       path        extensions            expectedResult
        return array(
            'Add to self'                               => array('C:.',      array('foo'),         'C:.foo'),
            'Empty extension'                           => array('C:foo',    array(''),            'C:foo.'),
            'Whitespace extension'                      => array('C:foo',    array(' '),           'C:foo. '),
            'Single extension'                          => array('C:foo',    array('bar'),         'C:foo.bar'),
            'Multiple extensions'                       => array('C:foo',    array('bar', 'baz'),  'C:foo.bar.baz'),
            'Empty extension with trailing slash'       => array('C:/foo/',  array(''),            'C:/foo.'),
            'Multiple extensions with trailing slash'   => array('C:/foo/',  array('bar', 'baz'),  'C:/foo.bar.baz'),
        );
    }

    /**
     * @dataProvider joinExtensionsData
     */
    public function testJoinExtensions($pathString, array $extensions, $expectedResultString)
    {
        $path = $this->factory->create($pathString);
        $result = call_user_func_array(array($path, 'joinExtensions'), $extensions);

        $this->assertSame($expectedResultString, $result->string());
    }

    public function testJoinExtensionsFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('C:foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo.bar/baz'. Path atoms must not contain separators."
        );
        $path->joinExtensions('bar/baz');
    }

    /**
     * @dataProvider joinExtensionsData
     */
    public function testJoinExtensionSequence($pathString, array $extensions, $expectedResultString)
    {
        $path = $this->factory->create($pathString);
        $result = $path->joinExtensionSequence($extensions);

        $this->assertSame($expectedResultString, $result->string());
    }

    public function testJoinExtensionSequenceWithNonArray()
    {
        $path = $this->factory->create('C:foo');
        $result = $path->joinExtensionSequence(new ArrayIterator(array('bar', 'baz')));

        $this->assertSame('C:foo.bar.baz', $result->string());
    }

    public function testJoinExtensionSequenceFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo.bar/baz'. Path atoms must not contain separators."
        );
        $path->joinExtensionSequence(array('bar/baz'));
    }

    public function suffixNameData()
    {
        //                                                 path           suffix       expectedResult
        return array(
            'Self'                                => array('C:.',         'foo',       'C:foo'),
            'Empty suffix'                        => array('C:foo/bar',   '',          'C:foo/bar'),
            'Empty suffix and trailing slash'     => array('C:foo/bar/',  '',          'C:foo/bar'),
            'Whitespace suffix'                   => array('C:foo/bar',   ' ',         'C:foo/bar '),
            'Normal suffix'                       => array('C:foo/bar',   '-baz',      'C:foo/bar-baz'),
            'Suffix with dots'                    => array('C:foo/bar',   '.baz.qux',  'C:foo/bar.baz.qux'),
            'Suffix with dots and trailing slash' => array('C:foo/bar',   '.baz.qux',  'C:foo/bar.baz.qux'),
        );
    }

    /**
     * @dataProvider suffixNameData
     */
    public function testSuffixName($pathString, $suffix, $expectedResultString)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($expectedResultString, $path->suffixName($suffix)->string());
    }

    public function testSuffixNameFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo/bar'. Path atoms must not contain separators."
        );
        $path->suffixName('/bar');
    }

    public function prefixNameData()
    {
        //                                                    path           prefix       expectedResult
        return array(
            'Self'                                   => array('C:.',         'foo',       'C:foo'),
            'Empty atom and trailing slash'          => array('C:./',        'foo',       'C:foo'),
            'Empty prefix'                           => array('C:foo/bar',   '',          'C:foo/bar'),
            'Whitespace prefix'                      => array('C:foo/bar',   ' ',         'C:foo/ bar'),
            'Normal prefix'                          => array('C:foo/bar',   'baz-',      'C:foo/baz-bar'),
            'Prefix with dots'                       => array('C:foo/bar',   'baz.qux.',  'C:foo/baz.qux.bar'),
            'Prefix with dots with trailing slash'   => array('C:foo/bar/',  'baz.qux.',  'C:foo/baz.qux.bar'),
        );
    }

    /**
     * @dataProvider prefixNameData
     */
    public function testPrefixName($pathString, $prefix, $expectedResultString)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($expectedResultString, $path->prefixName($prefix)->string());
    }

    public function testPrefixNameFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'bar/foo'. Path atoms must not contain separators."
        );
        $path->prefixName('bar/');
    }

    public function replaceData()
    {
        //                                              path                  offset  replacement              length  expectedResult
        return array(
            'Replace single atom implicit'     => array('C:foo/bar/baz/qux',  2,      array('doom'),           null,   'C:foo/bar/doom'),
            'Replace multiple atoms implicit'  => array('C:foo/bar/baz/qux',  1,      array('doom', 'splat'),  null,   'C:foo/doom/splat'),
            'Replace single atom explicit'     => array('C:foo/bar/baz/qux',  1,      array('doom'),           2,      'C:foo/doom/qux'),
            'Replace multiple atoms explicit'  => array('C:foo/bar/baz/qux',  1,      array('doom', 'splat'),  1,      'C:foo/doom/splat/baz/qux'),
            'Replace atoms past end'           => array('C:foo/bar/baz/qux',  111,    array('doom'),           222,    'C:foo/bar/baz/qux/doom'),
        );
    }

    /**
     * @dataProvider replaceData
     */
    public function testReplace($pathString, $offset, $replacement, $length, $expectedResultString)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame(
            $expectedResultString,
            $path->replace($offset, $replacement, $length)->string()
        );
    }

    public function testReplaceWithNonArray()
    {
        $path = $this->factory->create('C:foo/bar/baz/qux');
        $result = $path->replace(1, new ArrayIterator(array('doom', 'splat')), 1);

        $this->assertSame('C:foo/doom/splat/baz/qux', $result->string());
    }

    public function testReplaceFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('C:foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'bar/'. Path atoms must not contain separators."
        );
        $path->replace(1, array('bar/'));
    }

    public function replaceNameData()
    {
        //                                             path              name         expectedResult
        return array(
            'Self'                            => array('C:.',            'foo',       'C:foo'),
            'Empty name'                      => array('C:foo/bar',      '',          'C:foo'),
            'Empty name with trailing slash'  => array('C:foo/bar/',     '',          'C:foo'),
            'Whitespace name'                 => array('C:foo/bar',      ' ',         'C:foo/ '),
            'Normal name'                     => array('C:foo.bar.baz',  'qux',       'C:qux'),
            'Normal name with extensions'     => array('C:foo.bar.baz',  'qux.doom',  'C:qux.doom'),
        );
    }

    /**
     * @dataProvider replaceNameData
     */
    public function testReplaceName($pathString, $name, $expectedResultString)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame(
            $expectedResultString,
            $path->replaceName($name)->string()
        );
    }

    public function testReplaceNameFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('C:foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'bar/'. Path atoms must not contain separators."
        );
        $path->replaceName('bar/');
    }

    public function replaceNameWithoutExtensionData()
    {
        //                                             path              name         expectedResult
        return array(
            'Self'                            => array('C:.',            'foo',       'C:foo.'),
            'Empty name'                      => array('C:foo/bar',      '',          'C:foo'),
            'Empty name with trailing slash'  => array('C:foo/bar/',     '',          'C:foo'),
            'Whitespace name'                 => array('C:foo/bar',      ' ',         'C:foo/ '),
            'Normal name'                     => array('C:foo.bar.baz',  'qux',       'C:qux.baz'),
            'Normal name with extensions'     => array('C:foo.bar.baz',  'qux.doom',  'C:qux.doom.baz'),
        );
    }

    /**
     * @dataProvider replaceNameWithoutExtensionData
     */
    public function testReplaceNameWithoutExtension($pathString, $name, $expectedResultString)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame(
            $expectedResultString,
            $path->replaceNameWithoutExtension($name)->string()
        );
    }

    public function testReplaceNameWithoutExtensionFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('C:foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'qux/.baz'. Path atoms must not contain separators."
        );
        $path->replaceNameWithoutExtension('qux/');
    }

    public function replaceNamePrefixData()
    {
        //                                             path              name         expectedResult
        return array(
            'Self'                            => array('C:.',            'foo',       'C:foo.'),
            'Empty name'                      => array('C:foo/bar',      '',          'C:foo'),
            'Empty name with trailing slash'  => array('C:foo/bar/',     '',          'C:foo'),
            'Whitespace name'                 => array('C:foo/bar',      ' ',         'C:foo/ '),
            'Normal name'                     => array('C:foo.bar.baz',  'qux',       'C:qux.bar.baz'),
            'Normal name with extensions'     => array('C:foo.bar.baz',  'qux.doom',  'C:qux.doom.bar.baz'),
        );
    }

    /**
     * @dataProvider replaceNamePrefixData
     */
    public function testReplaceNamePrefix($pathString, $name, $expectedResultString)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame(
            $expectedResultString,
            $path->replaceNamePrefix($name)->string()
        );
    }

    public function testReplaceNamePrefixFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('C:foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'qux/.bar.baz'. Path atoms must not contain separators."
        );
        $path->replaceNamePrefix('qux/');
    }

    public function replaceNameSuffixData()
    {
        //                                             path              name         expectedResult
        return array(
            'Self'                            => array('C:.',            'foo',       'C:.foo'),
            'Empty name'                      => array('C:foo/bar',      '',          'C:foo/bar.'),
            'Empty name with trailing slash'  => array('C:foo/bar/',     '',          'C:foo/bar.'),
            'Whitespace name'                 => array('C:foo/bar',      ' ',         'C:foo/bar. '),
            'Normal name'                     => array('C:foo.bar.baz',  'qux',       'C:foo.qux'),
            'Normal name with extensions'     => array('C:foo.bar.baz',  'qux.doom',  'C:foo.qux.doom'),
        );
    }

    /**
     * @dataProvider replaceNameSuffixData
     */
    public function testReplaceNameSuffix($pathString, $name, $expectedResultString)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame(
            $expectedResultString,
            $path->replaceNameSuffix($name)->string()
        );
    }

    public function testReplaceNameSuffixFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('C:foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo.qux/'. Path atoms must not contain separators."
        );
        $path->replaceNameSuffix('qux/');
    }

    public function replaceExtensionData()
    {
        //                                             path              name         expectedResult
        return array(
            'Self'                            => array('C:.',            'foo',       'C:.foo'),
            'Empty name'                      => array('C:foo/bar',      '',          'C:foo/bar.'),
            'Empty name with trailing slash'  => array('C:foo/bar/',     '',          'C:foo/bar.'),
            'Whitespace name'                 => array('C:foo/bar',      ' ',         'C:foo/bar. '),
            'Normal name'                     => array('C:foo.bar.baz',  'qux',       'C:foo.bar.qux'),
            'Normal name with extensions'     => array('C:foo.bar.baz',  'qux.doom',  'C:foo.bar.qux.doom'),
        );
    }

    /**
     * @dataProvider replaceExtensionData
     */
    public function testReplaceExtension($pathString, $name, $expectedResultString)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame(
            $expectedResultString,
            $path->replaceExtension($name)->string()
        );
    }

    public function testReplaceExtensionFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('C:foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo.bar.qux/'. Path atoms must not contain separators."
        );
        $path->replaceExtension('qux/');
    }

    public function replaceNameAtomsData()
    {
        //                                              path                  offset  replacement              length  expectedResult
        return array(
            'Replace single atom implicit'     => array('C:foo.bar.baz.qux',  2,      array('doom'),           null,   'C:foo.bar.doom'),
            'Replace multiple atoms implicit'  => array('C:foo.bar.baz.qux',  1,      array('doom', 'splat'),  null,   'C:foo.doom.splat'),
            'Replace single atom explicit'     => array('C:foo.bar.baz.qux',  1,      array('doom'),           2,      'C:foo.doom.qux'),
            'Replace multiple atoms explicit'  => array('C:foo.bar.baz.qux',  1,      array('doom', 'splat'),  1,      'C:foo.doom.splat.baz.qux'),
            'Replace atoms past end'           => array('C:foo.bar.baz.qux',  111,    array('doom'),           222,    'C:foo.bar.baz.qux.doom'),
        );
    }

    /**
     * @dataProvider replaceNameAtomsData
     */
    public function testReplaceAtoms($pathString, $offset, $replacement, $length, $expectedResultString)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame(
            $expectedResultString,
            $path->replaceNameAtoms($offset, $replacement, $length)->string()
        );
    }

    public function testReplaceAtomsWithNonArray()
    {
        $path = $this->factory->create('C:foo.bar.baz.qux');
        $result = $path->replaceNameAtoms(1, new ArrayIterator(array('doom', 'splat')), 1);

        $this->assertSame('C:foo.doom.splat.baz.qux', $result->string());
    }

    public function toAbsoluteData()
    {
        //                            path        expected
        return array(
            'Single atom'    => array('C:foo',      'C:/foo'),
            'Multiple atoms' => array('C:foo/bar',  'C:/foo/bar'),
            'Trailing slash' => array('C:foo/bar/', 'C:/foo/bar/'),
        );
    }

    /**
     * @dataProvider toAbsoluteData
     */
    public function testToAbsolute($pathString, $expected)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($expected, $path->toAbsolute()->string());
    }

    public function testToAbsoluteFailureNoDrive()
    {
        $path = $this->factory->create('foo/bar');

        $this->setExpectedException('Eloquent\Pathogen\Exception\InvalidPathStateException');
        $path->toAbsolute();
    }

    public function testToAbsoluteFailureAnchored()
    {
        $path = $this->factory->create('/foo/bar');

        $this->setExpectedException('Eloquent\Pathogen\Exception\InvalidPathStateException');
        $path->toAbsolute();
    }

    public function testToRelative()
    {
        $path = $this->factory->create('path/to/foo');

        $this->assertSame($path, $path->toRelative());
    }

    public function testNormalize()
    {
        $path = $this->factory->create('foo/../bar');
        $normalizedPath = $this->factory->create('bar');

        $this->assertEquals($normalizedPath, $path->normalize());
    }

    // tests for RelativePathInterface implementation ==========================

    public function isSelfData()
    {
        //                                             path           isSelf
        return array(
            'Self'                            => array('.',           true),
            'Self non-normalized'             => array('./foo/..',    true),
            'Single atom'                     => array('foo',         false),
            'Multiple atoms'                  => array('foo/bar',     false),

            'Self with drive'                 => array('C:.',         false),
            'Self non-normalized with drive'  => array('C:./foo/..',  false),
            'Single atom with drive'          => array('C:foo',       false),
            'Multiple atoms with drive'       => array('C:foo/bar',   false),

            'Self anchored'                   => array('/.',          false),
            'Self non-normalized anchored'    => array('/./foo/..',   false),
            'Single atom anchored'            => array('/foo',        false),
            'Multiple atoms anchored'         => array('/foo/bar',    false),

        );
    }

    /**
     * @dataProvider isSelfData
     */
    public function testIsSelf($pathString, $isSelf)
    {
        $path = $this->factory->create($pathString);

        $this->assertTrue($isSelf === $path->isSelf());
    }

    public function resolveAgainstRelativePathData()
    {
        //                                                                                        basePath        path         expectedResult
        return array(
            'Root against single atom'                                                   => array('C:/',          'foo',       'C:/foo'),
            'Single atom against single atom'                                            => array('C:/foo',       'bar',       'C:/foo/bar'),
            'Multiple atoms against single atom'                                         => array('C:/foo/bar',   'baz',       'C:/foo/bar/baz'),
            'Multiple atoms with slash against single atoms'                             => array('C:/foo/bar/',  'baz',       'C:/foo/bar/baz'),
            'Multiple atoms against multiple atoms'                                      => array('C:/foo/bar',   'baz/qux',   'C:/foo/bar/baz/qux'),
            'Multiple atoms with slash against multiple atoms'                           => array('C:/foo/bar/',  'baz/qux',   'C:/foo/bar/baz/qux'),
            'Multiple atoms with slash against multiple atoms with slash'                => array('C:/foo/bar/',  'baz/qux/',  'C:/foo/bar/baz/qux'),
            'Root against parent atom'                                                   => array('C:/',          '..',        'C:/..'),
            'Single atom against parent atom'                                            => array('C:/foo',       '..',        'C:/foo/..'),
            'Single atom with slash against parent atom'                                 => array('C:/foo/',      '..',        'C:/foo/..'),
            'Single atom with slash against parent atom with slash'                      => array('C:/foo/',      '../',       'C:/foo/..'),
            'Multiple atoms against parent and single atom'                              => array('C:/foo/bar',   '../baz',    'C:/foo/bar/../baz'),
            'Multiple atoms with slash against parent atom and single atom'              => array('C:/foo/bar/',  '../baz',    'C:/foo/bar/../baz'),
            'Multiple atoms with slash against parent atom and single atom with slash'   => array('C:/foo/bar/',  '../baz/',   'C:/foo/bar/../baz'),
        );
    }

    /**
     * @dataProvider resolveAgainstRelativePathData
     */
    public function testResolveAgainstRelativePaths($basePathString, $pathString, $expectedResult)
    {
        $basePath = $this->factory->create($basePathString);
        $path = $this->factory->create($pathString);
        $resolved = $path->resolveAgainst($basePath);

        $this->assertSame($expectedResult, $resolved->string());
    }

    // Static methods ==========================================================

    public function createData()
    {
        //                                                                          path                      atoms                            drive isAnchored hasTrailingSeparator
        return array(
            'Anchored relative root'                                       => array('/',                      array(),                         null, true,      false),
            'Anchored relative'                                            => array('/foo/bar',               array('foo', 'bar'),             null, true,      false),
            'Anchored relative with trailing separator'                    => array('/foo/bar/',              array('foo', 'bar'),             null, true,      true),
            'Anchored relative with empty atoms'                           => array('/foo//bar',              array('foo', 'bar'),             null, true,      false),
            'Anchored relative with empty atoms at start'                  => array('//foo',                  array('foo'),                    null, true,      false),
            'Anchored relative with empty atoms at end'                    => array('/foo//',                 array('foo'),                    null, true,      true),
            'Anchored relative with whitespace atoms'                      => array('/ foo bar / baz qux ',   array(' foo bar ', ' baz qux '), null, true,      false),

            'Empty'                                                        => array('',                       array('.'),                      null, false,     false),
            'Self'                                                         => array('.',                      array('.'),                      null, false,     false),
            'Relative'                                                     => array('foo/bar',                array('foo', 'bar'),             null, false,     false),
            'Relative with trailing separator'                             => array('foo/bar/',               array('foo', 'bar'),             null, false,     true),
            'Relative with empty atoms'                                    => array('foo//bar',               array('foo', 'bar'),             null, false,     false),
            'Relative with empty atoms at end'                             => array('foo/bar//',              array('foo', 'bar'),             null, false,     true),
            'Relative with whitespace atoms'                               => array(' foo bar / baz qux ',    array(' foo bar ', ' baz qux '), null, false,     false),

            'Self with drive'                                              => array('C:.',                    array('.'),                      'C',  false,     false),
            'Relative with drive'                                          => array('C:foo/bar',              array('foo', 'bar'),             'C',  false,     false),
            'Relative with trailing separator with drive'                  => array('C:foo/bar/',             array('foo', 'bar'),             'C',  false,     true),
            'Relative with empty atoms with drive'                         => array('C:foo//bar',             array('foo', 'bar'),             'C',  false,     false),
            'Relative with empty atoms at end with drive'                  => array('C:foo/bar//',            array('foo', 'bar'),             'C',  false,     true),
            'Relative with whitespace atoms with drive'                    => array('C: foo bar / baz qux ',  array(' foo bar ', ' baz qux '), 'C',  false,     false),

            'Anchored relative root with backslashes'                      => array('\\',                     array(),                         null, true,      false),
            'Anchored relative with backslashes'                           => array('\foo\bar',               array('foo', 'bar'),             null, true,      false),
            'Anchored relative with trailing separator with backslashes'   => array('\foo\bar\\',             array('foo', 'bar'),             null, true,      true),
            'Anchored relative with empty atoms with backslashes'          => array('\foo\\\\bar',            array('foo', 'bar'),             null, true,      false),
            'Anchored relative with empty atoms at start with backslashes' => array('\\\\foo',                array('foo'),                    null, true,      false),
            'Anchored relative with empty atoms at end with backslashes'   => array('\foo\\\\',               array('foo'),                    null, true,      true),
            'Anchored relative with whitespace atoms with backslashes'     => array('\ foo bar \ baz qux ',   array(' foo bar ', ' baz qux '), null, true,      false),

            'Empty with backslashes'                                       => array('',                       array('.'),                      null, false,     false),
            'Self with backslashes'                                        => array('.',                      array('.'),                      null, false,     false),
            'Relative with backslashes'                                    => array('foo\bar',                array('foo', 'bar'),             null, false,     false),
            'Relative with trailing separator with backslashes'            => array('foo\bar\\',              array('foo', 'bar'),             null, false,     true),
            'Relative with empty atoms with backslashes'                   => array('foo\\\\bar',             array('foo', 'bar'),             null, false,     false),
            'Relative with empty atoms at end with backslashes'            => array('foo\bar\\\\',            array('foo', 'bar'),             null, false,     true),
            'Relative with whitespace atoms with backslashes'              => array(' foo bar \ baz qux ',    array(' foo bar ', ' baz qux '), null, false,     false),

            'Self with drive with backslashes'                             => array('C:.',                    array('.'),                      'C',  false,     false),
            'Relative with drive with backslashes'                         => array('C:foo\bar',              array('foo', 'bar'),             'C',  false,     false),
            'Relative with trailing separator with drive with backslashes' => array('C:foo\bar\\',            array('foo', 'bar'),             'C',  false,     true),
            'Relative with empty atoms with drive with backslashes'        => array('C:foo\\\\bar',           array('foo', 'bar'),             'C',  false,     false),
            'Relative with empty atoms at end with drive with backslashes' => array('C:foo\bar\\\\',          array('foo', 'bar'),             'C',  false,     true),
            'Relative with whitespace atoms with drive with backslashes'   => array('C: foo bar \ baz qux ',  array(' foo bar ', ' baz qux '), 'C',  false,     false),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testFromString($pathString, array $atoms, $drive, $isAnchored, $hasTrailingSeparator)
    {
        $path = RelativeWindowsPath::fromString($pathString);

        $this->assertSame($atoms, $path->atoms());
        $this->assertTrue($path instanceof RelativeWindowsPath);
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    public function testFromAtoms()
    {
        $path = RelativeWindowsPath::fromAtoms(array('foo', 'bar'), true);

        $this->assertSame(array('foo', 'bar'), $path->atoms());
        $this->assertTrue($path instanceof RelativeWindowsPath);
        $this->assertTrue($path->hasTrailingSeparator());
    }

    public function testFromAtomsDefaults()
    {
        $path = RelativeWindowsPath::fromAtoms(array('foo', 'bar'));

        $this->assertSame(array('foo', 'bar'), $path->atoms());
        $this->assertTrue($path instanceof RelativeWindowsPath);
        $this->assertFalse($path->hasTrailingSeparator());
    }

    /**
     * @dataProvider createData
     */
    public function testFromDriveAndAtoms(
        $pathString,
        array $atoms,
        $drive,
        $isAnchored,
        $hasTrailingSeparator
    ) {
        $path = RelativeWindowsPath::fromDriveAndAtoms(
            $atoms,
            $drive,
            $isAnchored,
            $hasTrailingSeparator
        );

        $this->assertSame($atoms, $path->atoms());
        $this->assertTrue($path instanceof RelativeWindowsPath);
        $this->assertSame($isAnchored, $path->isAnchored());
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    public function testFromDriveAndAtomsDefaults()
    {
        $path = RelativeWindowsPath::fromDriveAndAtoms(array('.'));

        $this->assertSame(array('.'), $path->atoms());
        $this->assertTrue($path instanceof RelativeWindowsPath);
        $this->assertFalse($path->isAnchored());
        $this->assertFalse($path->hasTrailingSeparator());
    }

    // Implementation details ==================================================

    public function testPathDriveSpecifierNonWindowsPath()
    {
        $path = $this->factory->create('C:foo/bar');
        $regularPath = $this->regularPathFactory->create('/foo/bar');

        $this->assertNull(Liberator::liberate($path)->pathDriveSpecifier($regularPath));
    }

    public function testCreatePathAbsolutePath()
    {
        $path = $this->factory->create('C:foo/bar');
        $createdPath = Liberator::liberate($path)->createPath(array('baz', 'qux'), true, true);

        $this->assertSame(array('baz', 'qux'), $createdPath->atoms());
        $this->assertTrue($createdPath instanceof AbsoluteWindowsPath);
        $this->assertTrue($createdPath->hasTrailingSeparator());
    }
}
