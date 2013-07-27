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
use Eloquent\Pathogen\Factory\PathFactory;
use Phake;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Pathogen\Windows\AbsoluteWindowsPath
 * @covers \Eloquent\Pathogen\FileSystem\AbstractAbsoluteFileSystemPath
 * @covers \Eloquent\Pathogen\AbsolutePath
 * @covers \Eloquent\Pathogen\AbstractPath
 */
class AbsoluteWindowsPathTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new Factory\WindowsPathFactory;
        $this->regularPathFactory = new PathFactory;
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

    // tests for AbsoluteWindowsPathInterface implementation ===================

    public function joinDriveData()
    {
        //                                      path           drive  expectedResult
        return array(
            'Join root'                => array('/',           'C',   'C:/'),
            'Join drive-less'          => array('/foo/bar',    'C',   'C:/foo/bar'),
            'Join with existing drive' => array('D:/foo/bar',  'C',   'C:/foo/bar'),
            'Join with trailing slash' => array('/foo/bar/',   'C',   'C:/foo/bar'),
        );
    }

    /**
     * @dataProvider joinDriveData
     */
    public function testJoinDrive($pathString, $drive, $expectedResultString)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($expectedResultString, $path->joinDrive($drive)->string());
    }

    // tests for PathInterface implementation ==================================

    public function pathData()
    {
        //                                       path                       drive  atoms                              hasTrailingSeparator
        return array(
            'Root'                      => array('/',                       null,  array(),                           false),
            'Single atom'               => array('/foo',                    null,  array('foo'),                      false),
            'Trailing slash'            => array('/foo/',                   null,  array('foo'),                      true),
            'Multiple atoms'            => array('/foo/bar',                null,  array('foo', 'bar'),               false),
            'Parent atom'               => array('/foo/../bar',             null,  array('foo', '..', 'bar'),         false),
            'Self atom'                 => array('/foo/./bar',              null,  array('foo', '.', 'bar'),          false),
            'Whitespace'                => array('/ foo bar / baz qux ',    null,  array(' foo bar ', ' baz qux '),   false),

            'Root with drive'           => array('C:/',                     'C',   array(),                           false),
            'Single atom with drive'    => array('C:/foo',                  'C',   array('foo'),                      false),
            'Trailing slash with drive' => array('C:/foo/',                 'C',   array('foo'),                      true),
            'Multiple atoms with drive' => array('C:/foo/bar',              'C',   array('foo', 'bar'),               false),
            'Parent atom with drive'    => array('C:/foo/../bar',           'C',   array('foo', '..', 'bar'),         false),
            'Self atom with drive'      => array('C:/foo/./bar',            'C',   array('foo', '.', 'bar'),          false),
            'Whitespace with drive'     => array('C:/ foo bar / baz qux ',  'C',   array(' foo bar ', ' baz qux '),   false),
        );
    }

    /**
     * @dataProvider pathData
     */
    public function testConstructor($pathString, $drive, array $atoms, $hasTrailingSeparator)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($drive, $path->drive());
        $this->assertSame(null !== $drive, $path->hasDrive());
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
        $this->assertSame($pathString, $path->string());
        $this->assertSame($pathString, strval($path->string()));
    }

    public function testConstructorDefaults()
    {
        $this->path = new AbsoluteWindowsPath(array(), null);

        $this->assertFalse($this->path->hasTrailingSeparator());
    }

    public function testConstructorFailureAtomContainingSeparator()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo/bar'. Path atoms must not contain separators."
        );
        new AbsoluteWindowsPath(array('foo/bar'), null);
    }

    public function testConstructorFailureAtomContainingBackslash()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo\\\\bar'. Path atoms must not contain separators."
        );
        new AbsoluteWindowsPath(array('foo\bar'), null);
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
        new AbsoluteWindowsPath(array(sprintf('foo%sbar', $character)), null);
    }

    public function testConstructorFailureEmptyAtom()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\EmptyPathAtomException'
        );
        new AbsoluteWindowsPath(array(''), null);
    }

    public function testConstructorFailureInvalidDriveSpecifierCharacter()
    {
        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\InvalidDriveSpecifierException'
        );
        new AbsoluteWindowsPath(array(), '$');
    }

    public function testConstructorFailureInvalidDriveSpecifierLength()
    {
        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\InvalidDriveSpecifierException'
        );
        new AbsoluteWindowsPath(array(), 'CC');
    }

    public function sliceAtomsData()
    {
        //                                             path                   index  length  expectedResult
        return array(
            'Slice till end'                  => array('/foo/bar/baz/qux',    1,     null,   array('bar', 'baz', 'qux')),
            'Slice specific range'            => array('/foo/bar/baz/qux',    1,     2,      array('bar', 'baz')),

            'Slice till end with drive'       => array('C:/foo/bar/baz/qux',  1,     null,   array('bar', 'baz', 'qux')),
            'Slice specific range with drive' => array('C:/foo/bar/baz/qux',  1,     2,      array('bar', 'baz')),
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
        //                                                        path               name            nameWithoutExtension  namePrefix  nameSuffix  extension
        return array(
            'Root'                                       => array('/',               '',             '',                   '',         null,       null),
            'No extensions'                              => array('/foo',            'foo',          'foo',                'foo',      null,       null),
            'Empty extension'                            => array('/foo.',           'foo.',         'foo',                'foo',      '',         ''),
            'Whitespace extension'                       => array('/foo. ',          'foo. ',        'foo',                'foo',      ' ',        ' '),
            'Single extension'                           => array('/foo.bar',        'foo.bar',      'foo',                'foo',      'bar',      'bar'),
            'Multiple extensions'                        => array('/foo.bar.baz',    'foo.bar.baz',  'foo.bar',            'foo',      'bar.baz',  'baz'),
            'No name with single extension'              => array('/.foo',           '.foo',         '',                   '',         'foo',      'foo'),
            'No name with multiple extension'            => array('/.foo.bar',       '.foo.bar',     '.foo',               '',         'foo.bar',  'bar'),

            'Root with drive'                            => array('C:/',             '',             '',                   '',         null,       null),
            'No extensions with drive'                   => array('C:/foo',          'foo',          'foo',                'foo',      null,       null),
            'Empty extension with drive'                 => array('C:/foo.',         'foo.',         'foo',                'foo',      '',         ''),
            'Whitespace extension with drive'            => array('C:/foo. ',        'foo. ',        'foo',                'foo',      ' ',        ' '),
            'Single extension with drive'                => array('C:/foo.bar',      'foo.bar',      'foo',                'foo',      'bar',      'bar'),
            'Multiple extensions with drive'             => array('C:/foo.bar.baz',  'foo.bar.baz',  'foo.bar',            'foo',      'bar.baz',  'baz'),
            'No name with single extension with drive'   => array('C:/.foo',         '.foo',         '',                   '',         'foo',      'foo'),
            'No name with multiple extension with drive' => array('C:/.foo.bar',     '.foo.bar',     '.foo',               '',         'foo.bar',  'bar'),
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
        //                                             path           nameAtoms
        return array(
            'Root'                            => array('/',           array('')),
            'Root with self'                  => array('/.',          array('', '')),
            'Single name atom'                => array('/foo',        array('foo')),
            'Multiple name atoms'             => array('/foo.bar',    array('foo', 'bar')),
            'Multiple path atoms'             => array('/foo/bar',    array('bar')),

            'Root with drive'                 => array('C:/',         array('')),
            'Root with self with drive'       => array('C:/.',        array('', '')),
            'Single name atom with drive'     => array('C:/foo',      array('foo')),
            'Multiple name atoms with drive'  => array('C:/foo.bar',  array('foo', 'bar')),
            'Multiple path atoms with drive'  => array('C:/foo/bar',  array('bar')),
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

    public function sliceNameAtomsData()
    {
        //                                             path                   index  length  expectedResult
        return array(
            'Slice till end'                  => array('/foo.bar.baz.qux',    1,     null,   array('bar', 'baz', 'qux')),
            'Slice specific range'            => array('/foo.bar.baz.qux',    1,     2,      array('bar', 'baz')),

            'Slice till end with drive'       => array('C:/foo.bar.baz.qux',  1,     null,   array('bar', 'baz', 'qux')),
            'Slice specific range with drive' => array('C:/foo.bar.baz.qux',  1,     2,      array('bar', 'baz')),
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
        //                                                  path                   needle         caseSensitive  expectedResult
        return array(
            'Empty'                                => array('/',                   '',            null,          true),
            'Prefix'                               => array('/foo/bar/baz.qux',    '/FOO/BAR',    null,          true),
            'Middle'                               => array('/foo/bar/baz.qux',    'BAR/BAZ',     null,          true),
            'Suffix'                               => array('/foo/bar/baz.qux',    '/BAZ.QUX',    null,          true),
            'Not found'                            => array('/foo/bar/baz.qux',    'DOOM',        null,          false),

            'Empty case sensitive'                 => array('/',                   '',            true,          true),
            'Prefix case sensitive'                => array('/foo/bar/baz.qux',    '/foo/bar',    true,          true),
            'Middle case sensitive'                => array('/foo/bar/baz.qux',    'bar/baz',     true,          true),
            'Suffix case sensitive'                => array('/foo/bar/baz.qux',    '/baz.qux',    true,          true),
            'Not found case sensitive'             => array('/foo/bar/baz.qux',    'FOO',         true,          false),

            'Empty with drive'                     => array('C:/',                 '',            null,          true),
            'Prefix with drive'                    => array('C:/foo/bar/baz.qux',  'c:/FOO/BAR',  null,          true),
            'Middle with drive'                    => array('C:/foo/bar/baz.qux',  'BAR/BAZ',     null,          true),
            'Suffix with drive'                    => array('C:/foo/bar/baz.qux',  '/BAZ.QUX',    null,          true),
            'Not found with drive'                 => array('C:/foo/bar/baz.qux',  'DOOM',        null,          false),

            'Empty case sensitive with drive'      => array('C:/',                 '',            true,          true),
            'Prefix case sensitive with drive'     => array('C:/foo/bar/baz.qux',  'C:/foo/bar',  true,          true),
            'Middle case sensitive with drive'     => array('C:/foo/bar/baz.qux',  'bar/baz',     true,          true),
            'Suffix case sensitive with drive'     => array('C:/foo/bar/baz.qux',  '/baz.qux',    true,          true),
            'Not found case sensitive with drive'  => array('C:/foo/bar/baz.qux',  'FOO',         true,          false),
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
        //                                                  path                  needle          caseSensitive  expectedResult
        return array(
            'Empty'                                => array('/',                  '',             null,          true),
            'Prefix'                               => array('/foo/bar/baz.qux',   '/FOO/BAR',     null,          true),
            'Middle'                               => array('/foo/bar/baz.qux',   'BAR/BAZ',      null,          false),
            'Suffix'                               => array('/foo/bar/baz.qux',   '/BAZ.QUX',     null,          false),
            'Not found'                            => array('/foo/bar/baz.qux',   'DOOM',         null,          false),

            'Empty case sensitive'                 => array('/',                  '',             true,          true),
            'Prefix case sensitive'                => array('/foo/bar/baz.qux',   '/foo/bar',     true,          true),
            'Middle case sensitive'                => array('/foo/bar/baz.qux',   'bar/baz',      true,          false),
            'Suffix case sensitive'                => array('/foo/bar/baz.qux',   '/baz.qux',     true,          false),
            'Not found case sensitive'             => array('/foo/bar/baz.qux',   'FOO',          true,          false),

            'Empty with drive'                     => array('C:/',                 '',            null,          true),
            'Prefix with drive'                    => array('C:/foo/bar/baz.qux',  'c:/FOO/BAR',  null,          true),
            'Middle with drive'                    => array('C:/foo/bar/baz.qux',  'BAR/BAZ',     null,          false),
            'Suffix with drive'                    => array('C:/foo/bar/baz.qux',  '/BAZ.QUX',    null,          false),
            'Not found with drive'                 => array('C:/foo/bar/baz.qux',  'DOOM',        null,          false),

            'Empty case sensitive with drive'      => array('C:/',                 '',            true,          true),
            'Prefix case sensitive with drive'     => array('C:/foo/bar/baz.qux',  'C:/foo/bar',  true,          true),
            'Middle case sensitive with drive'     => array('C:/foo/bar/baz.qux',  'bar/baz',     true,          false),
            'Suffix case sensitive with drive'     => array('C:/foo/bar/baz.qux',  '/baz.qux',    true,          false),
            'Not found case sensitive with drive'  => array('C:/foo/bar/baz.qux',  'FOO',         true,          false),
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
        //                                                  path                   needle         caseSensitive  expectedResult
        return array(
            'Empty'                                => array('/',                   '',            null,          true),
            'Prefix'                               => array('/foo/bar/baz.qux',    '/FOO/BAR',    null,          false),
            'Middle'                               => array('/foo/bar/baz.qux',    'BAR/BAZ',     null,          false),
            'Suffix'                               => array('/foo/bar/baz.qux',    '/BAZ.QUX',    null,          true),
            'Not found'                            => array('/foo/bar/baz.qux',    'DOOM',        null,          false),

            'Empty case sensitive'                 => array('/',                   '',            true,          true),
            'Prefix case sensitive'                => array('/foo/bar/baz.qux',    '/foo/bar',    true,          false),
            'Middle case sensitive'                => array('/foo/bar/baz.qux',    'bar/baz',     true,          false),
            'Suffix case sensitive'                => array('/foo/bar/baz.qux',    '/baz.qux',    true,          true),
            'Not found case sensitive'             => array('/foo/bar/baz.qux',    'FOO',         true,          false),

            'Empty with drive'                     => array('C:/',                 '',            null,          true),
            'Prefix with drive'                    => array('C:/foo/bar/baz.qux',  'c:/FOO/BAR',  null,          false),
            'Middle with drive'                    => array('C:/foo/bar/baz.qux',  'BAR/BAZ',     null,          false),
            'Suffix with drive'                    => array('C:/foo/bar/baz.qux',  '/BAZ.QUX',    null,          true),
            'Not found with drive'                 => array('C:/foo/bar/baz.qux',  'DOOM',        null,          false),

            'Empty case sensitive with drive'      => array('C:/',                 '',            true,          true),
            'Prefix case sensitive with drive'     => array('C:/foo/bar/baz.qux',  'C:/foo/bar',  true,          false),
            'Middle case sensitive with drive'     => array('C:/foo/bar/baz.qux',  'bar/baz',     true,          false),
            'Suffix case sensitive with drive'     => array('C:/foo/bar/baz.qux',  '/baz.qux',    true,          true),
            'Not found case sensitive with drive'  => array('C:/foo/bar/baz.qux',  'FOO',         true,          false),
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
        //                                                    path                   pattern          caseSensitive  flags          expectedResult
        return array(
            'Prefix'                                 => array('/foo/bar/baz.qux',    '/FOO/BAR*',     null,          null,          true),
            'Middle'                                 => array('/foo/bar/baz.qux',    '*BAR/BAZ*',     null,          null,          true),
            'Suffix'                                 => array('/foo/bar/baz.qux',    '*/BAZ.QUX',     null,          null,          true),
            'Surrounding'                            => array('/foo/bar/baz.qux',    '/FOO*.QUX',     null,          null,          true),
            'Single character'                       => array('/foo/bar/baz.qux',    '*B?R*',         null,          null,          true),
            'Single character no match'              => array('/foo/bar/baz.qux',    '*F?X*',         null,          null,          false),
            'Set'                                    => array('/foo/bar/baz.qux',    '*BA[RZ]*',      null,          null,          true),
            'Set no match'                           => array('/foo/bar/baz.qux',    '*BA[X]*',       null,          null,          false),
            'Negated set'                            => array('/foo/bar/baz.qux',    '*BA[!RX]*',     null,          null,          true),
            'Negated set no match'                   => array('/foo/bar/baz.qux',    '*BA[!RZ]*',     null,          null,          false),
            'Range'                                  => array('/foo/bar/baz.qux',    '*BA[A-R]*',     null,          null,          true),
            'Range no match'                         => array('/foo/bar/baz.qux',    '*BA[S-Y]*',     null,          null,          false),
            'Negated range'                          => array('/foo/bar/baz.qux',    '*BA[!S-Y]*',    null,          null,          true),
            'Negated range no match'                 => array('/foo/bar/baz.qux',    '*BA[!R-Z]*',    null,          null,          false),
            'No partial match'                       => array('/foo/bar/baz.qux',    'BAR',           null,          null,          false),
            'Not found'                              => array('/foo/bar/baz.qux',    'DOOM',          null,          null,          false),

            'Case sensitive'                         => array('/foo/bar/baz.qux',    '*bar/baz*',     true,          null,          true),
            'Case sensitive no match'                => array('/foo/bar/baz.qux',    '*FOO*',         true,          null,          false),
            'Special flags'                          => array('/foo/bar/baz.qux',    '/FOO/BAR/*',    false,         FNM_PATHNAME,  true),
            'Special flags no match'                 => array('/foo/bar/baz.qux',    '*FOO/BAR*',     false,         FNM_PATHNAME,  false),

            'Prefix with drive'                      => array('C:/foo/bar/baz.qux',  'c:/FOO/BAR*',   null,          null,          true),
            'Middle with drive'                      => array('C:/foo/bar/baz.qux',  '*BAR/BAZ*',     null,          null,          true),
            'Suffix with drive'                      => array('C:/foo/bar/baz.qux',  '*/BAZ.QUX',     null,          null,          true),
            'Surrounding with drive'                 => array('C:/foo/bar/baz.qux',  'c:/FOO*.QUX',   null,          null,          true),
            'Single character with drive'            => array('C:/foo/bar/baz.qux',  '*B?R*',         null,          null,          true),
            'Single character no match with drive'   => array('C:/foo/bar/baz.qux',  '*F?X*',         null,          null,          false),
            'Set with drive'                         => array('C:/foo/bar/baz.qux',  '*BA[RZ]*',      null,          null,          true),
            'Set no match with drive'                => array('C:/foo/bar/baz.qux',  '*BA[X]*',       null,          null,          false),
            'Negated set with drive'                 => array('C:/foo/bar/baz.qux',  '*BA[!RX]*',     null,          null,          true),
            'Negated set no match with drive'        => array('C:/foo/bar/baz.qux',  '*BA[!RZ]*',     null,          null,          false),
            'Range with drive'                       => array('C:/foo/bar/baz.qux',  '*BA[A-R]*',     null,          null,          true),
            'Range no match with drive'              => array('C:/foo/bar/baz.qux',  '*BA[S-Y]*',     null,          null,          false),
            'Negated range with drive'               => array('C:/foo/bar/baz.qux',  '*BA[!S-Y]*',    null,          null,          true),
            'Negated range no match with drive'      => array('C:/foo/bar/baz.qux',  '*BA[!R-Z]*',    null,          null,          false),
            'No partial match with drive'            => array('C:/foo/bar/baz.qux',  'BAR',           null,          null,          false),
            'Not found with drive'                   => array('C:/foo/bar/baz.qux',  'DOOM',          null,          null,          false),

            'Case sensitive with drive'              => array('C:/foo/bar/baz.qux',  '*bar/baz*',     true,          null,          true),
            'Case sensitive no match with drive'     => array('C:/foo/bar/baz.qux',  '*FOO*',         true,          null,          false),
            'Special flags with drive'               => array('C:/foo/bar/baz.qux',  'c:/FOO/BAR/*',  false,         FNM_PATHNAME,  true),
            'Special flags no match with drive'      => array('C:/foo/bar/baz.qux',  '*FOO/BAR*',     false,         FNM_PATHNAME,  false),
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
        //                                              path                   pattern              matches                                                  flags                 offset  expectedResult
        return array(
            'Match'                            => array('/foo/bar/baz.qux',    '{.*(FOO)/BAR.*}i',  array('/foo/bar/baz.qux', 'foo'),                        null,                 null,   true),
            'No match'                         => array('/foo/bar/baz.qux',    '{.*DOOM.*}i',       array(),                                                 null,                 null,   false),
            'Special flags'                    => array('/foo/bar/baz.qux',    '{.*(FOO)/BAR.*}i',  array(array('/foo/bar/baz.qux', 0), array('foo', 1)),    PREG_OFFSET_CAPTURE,  null,   true),
            'Match with offset'                => array('/foo/bar/baz.qux',    '{FOO}i',            array('foo'),                                            null,                 1,      true),
            'No match with offset'             => array('/foo/bar/baz.qux',    '{FOO}i',            array(),                                                 null,                 2,      false),

            'Match with drive'                 => array('C:/foo/bar/baz.qux',  '{.*(FOO)/BAR.*}i',  array('C:/foo/bar/baz.qux', 'foo'),                      null,                 null,   true),
            'No match with drive'              => array('C:/foo/bar/baz.qux',  '{.*DOOM.*}i',       array(),                                                 null,                 null,   false),
            'Special flags with drive'         => array('C:/foo/bar/baz.qux',  '{.*(FOO)/BAR.*}i',  array(array('C:/foo/bar/baz.qux', 0), array('foo', 3)),  PREG_OFFSET_CAPTURE,  null,   true),
            'Match with offset with drive'     => array('C:/foo/bar/baz.qux',  '{FOO}i',            array('foo'),                                            null,                 1,      true),
            'No match with offset with drive'  => array('C:/foo/bar/baz.qux',  '{FOO}i',            array(),                                                 null,                 4,      false),
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
        //                                       path                 needle      caseSensitive  expectedResult
        return array(
            'Empty'                     => array('/',                 '',         null,          true),
            'Prefix'                    => array('/foo/bar.baz.qux',  'BAR.BAZ',  null,          true),
            'Middle'                    => array('/foo/bar.baz.qux',  'BAZ',      null,          true),
            'Suffix'                    => array('/foo/bar.baz.qux',  'BAZ.QUX',  null,          true),
            'Not found'                 => array('/foo/bar.baz.qux',  'DOOM',     null,          false),
            'Match only in name'        => array('/foo/bar.baz.qux',  'foo',      null,          false),

            'Empty case sensitive'      => array('/',                 '',         true,          true),
            'Prefix case sensitive'     => array('/foo/bar.baz.qux',  'bar.baz',  true,          true),
            'Middle case sensitive'     => array('/foo/bar.baz.qux',  'baz',      true,          true),
            'Suffix case sensitive'     => array('/foo/bar.baz.qux',  'baz.qux',  true,          true),
            'Not found case sensitive'  => array('/foo/bar.baz.qux',  'BAR',      true,          false),
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
        //                                       path                 needle      caseSensitive  expectedResult
        return array(
            'Empty'                     => array('/',                 '',         null,          true),
            'Prefix'                    => array('/foo/bar.baz.qux',  'BAR.BAZ',  null,          true),
            'Middle'                    => array('/foo/bar.baz.qux',  'BAZ',      null,          false),
            'Suffix'                    => array('/foo/bar.baz.qux',  'BAZ.QUX',  null,          false),
            'Not found'                 => array('/foo/bar.baz.qux',  'DOOM',     null,          false),

            'Empty case sensitive'      => array('/',                 '',         true,          true),
            'Prefix case sensitive'     => array('/foo/bar.baz.qux',  'bar.baz',  true,          true),
            'Middle case sensitive'     => array('/foo/bar.baz.qux',  'baz',      true,          false),
            'Suffix case sensitive'     => array('/foo/bar.baz.qux',  'baz.qux',  true,          false),
            'Not found case sensitive'  => array('/foo/bar.baz.qux',  'BAR',      true,          false),
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
        //                                         path                 pattern        caseSensitive  flags        expectedResult
        return array(
            'Prefix'                      => array('/foo/bar.baz.qux',  'BAR.BAZ*',    null,          null,        true),
            'Middle'                      => array('/foo/bar.baz.qux',  '*BAZ*',       null,          null,        true),
            'Suffix'                      => array('/foo/bar.baz.qux',  '*BAZ.QUX',    null,          null,        true),
            'Surrounding'                 => array('/foo/bar.baz.qux',  'BAR.*.QUX',   null,          null,        true),
            'Single character'            => array('/foo/bar.baz.qux',  '*B?R*',       null,          null,        true),
            'Single character no match'   => array('/foo/bar.baz.qux',  '*B?X*',       null,          null,        false),
            'Set'                         => array('/foo/bar.baz.qux',  '*BA[RZ]*',    null,          null,        true),
            'Set no match'                => array('/foo/bar.baz.qux',  '*BA[X]*',     null,          null,        false),
            'Negated set'                 => array('/foo/bar.baz.qux',  '*BA[!RX]*',   null,          null,        true),
            'Negated set no match'        => array('/foo/bar.baz.qux',  '*BA[!RZ]*',   null,          null,        false),
            'Range'                       => array('/foo/bar.baz.qux',  '*BA[A-R]*',   null,          null,        true),
            'Range no match'              => array('/foo/bar.baz.qux',  '*BA[S-Y]*',   null,          null,        false),
            'Negated range'               => array('/foo/bar.baz.qux',  '*BA[!S-Y]*',  null,          null,        true),
            'Negated range no match'      => array('/foo/bar.baz.qux',  '*BA[!R-Z]*',  null,          null,        false),
            'No partial match'            => array('/foo/bar.baz.qux',  'BAZ',         null,          null,        false),
            'Not found'                   => array('/foo/bar.baz.qux',  'DOOM',        null,          null,        false),

            'Case sensitive'              => array('/foo/bar.baz.qux',  '*baz*',       true,          null,        true),
            'Case sensitive no match'     => array('/foo/bar.baz.qux',  '*BAZ*',       true,          null,        false),
            'Special flags'               => array('/foo/.bar.baz',     '.bar*',       false,         FNM_PERIOD,  true),
            'Special flags no match'      => array('/foo/.bar.baz',     '*bar*',       false,         FNM_PERIOD,  false),
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
        //                                   path                 pattern               matches                                           flags                 offset  expectedResult
        return array(
            'Match'                 => array('/foo/bar.baz.qux',  '{.*(BAR)\.BAZ.*}i',  array('bar.baz.qux', 'bar'),                      null,                 null,   true),
            'No match'              => array('/foo/bar.baz.qux',  '{.*DOOM.*}i',        array(),                                          null,                 null,   false),
            'Special flags'         => array('/foo/bar.baz.qux',  '{.*BAR\.(BAZ).*}i',  array(array('bar.baz.qux', 0), array('baz', 4)),  PREG_OFFSET_CAPTURE,  null,   true),
            'Match with offset'     => array('/foo/bar.baz.qux',  '{BAZ}i',             array('baz'),                                     null,                 4,      true),
            'No match with offset'  => array('/foo/bar.baz.qux',  '{BAZ}i',             array(),                                          null,                 5,      false),
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
        //                                        path               numLevels  parent
        return array(
            'Root'                       => array('/',               null,      '/'),
            'Single atom'                => array('/foo',            null,      '/'),
            'Multiple atoms'             => array('/foo/bar',        null,      '/foo'),
            'Up one level'               => array('/foo/bar/baz',    1,         '/foo/bar'),
            'Up two levels'              => array('/foo/bar/baz',    2,         '/foo'),

            'Root with drive'            => array('C:/',             null,      'C:/'),
            'Single atom with drive'     => array('C:/foo',          null,      'C:/'),
            'Multiple atoms with drive'  => array('C:/foo/bar',      null,      'C:/foo'),
            'Up one level with drive'    => array('C:/foo/bar/baz',  1,         'C:/foo/bar'),
            'Up two levels with drive'   => array('C:/foo/bar/baz',  2,         'C:/foo'),
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
        //                                          path            expectedResult
        return array(
            'Single atom'                  => array('/foo/',        '/foo'),
            'Multiple atoms'               => array('/foo/bar/',    '/foo/bar'),
            'Whitespace atoms'             => array('/foo/bar /',   '/foo/bar '),
            'No trailing slash'            => array('/foo',         '/foo'),
            'Root'                         => array('/',            '/'),

            'Single atom with drive'       => array('C:/foo/',      'C:/foo'),
            'Multiple atoms with drive'    => array('C:/foo/bar/',  'C:/foo/bar'),
            'Whitespace atoms with drive'  => array('C:/foo/bar /', 'C:/foo/bar '),
            'No trailing slash with drive' => array('C:/foo',       'C:/foo'),
            'Root with drive'              => array('C:/',          'C:/'),
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
        //                                              path               strippedExtension  strippedSuffix
        return array(
            'Root'                             => array('/',               '/',               '/'),
            'No extensions'                    => array('/foo',            '/foo',            '/foo'),
            'Empty extension'                  => array('/foo.',           '/foo',            '/foo'),
            'Whitespace extension'             => array('/foo . ',         '/foo ',           '/foo '),
            'Single extension'                 => array('/foo.bar',        '/foo',            '/foo'),
            'Multiple extensions'              => array('/foo.bar.baz',    '/foo.bar',        '/foo'),

            'Root with drive'                  => array('C:/',             'C:/',             'C:/'),
            'No extensions with drive'         => array('C:/foo',          'C:/foo',          'C:/foo'),
            'Empty extension with drive'       => array('C:/foo.',         'C:/foo',          'C:/foo'),
            'Whitespace extension with drive'  => array('C:/foo . ',       'C:/foo ',         'C:/foo '),
            'Single extension with drive'      => array('C:/foo.bar',      'C:/foo',          'C:/foo'),
            'Multiple extensions with drive'   => array('C:/foo.bar.baz',  'C:/foo.bar',      'C:/foo'),
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
        //                                                         path           atoms                 expectedResult
        return array(
            'Single atom to root'                         => array('/',           array('foo'),         '/foo'),
            'Multiple atoms to root'                      => array('/',           array('foo', 'bar'),  '/foo/bar'),
            'Multiple atoms to multiple atoms'            => array('/foo/bar',    array('baz', 'qux'),  '/foo/bar/baz/qux'),
            'Whitespace atoms'                            => array('/foo',        array(' '),           '/foo/ '),
            'Special atoms'                               => array('/foo',        array('.', '..'),     '/foo/./..'),

            'Single atom to root with drive'              => array('C:/',         array('foo'),         'C:/foo'),
            'Multiple atoms to root with drive'           => array('C:/',         array('foo', 'bar'),  'C:/foo/bar'),
            'Multiple atoms to multiple atoms with drive' => array('C:/foo/bar',  array('baz', 'qux'),  'C:/foo/bar/baz/qux'),
            'Whitespace atoms with drive'                 => array('C:/foo',      array(' '),           'C:/foo/ '),
            'Special atoms with drive'                    => array('C:/foo',      array('.', '..'),     'C:/foo/./..'),
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
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz/qux'. Path atoms must not contain separators."
        );
        $path->joinAtoms('bar', 'baz/qux');
    }

    public function testJoinAtomsFailureAtomContainingBackslash()
    {
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz\\\\qux'. Path atoms must not contain separators."
        );
        $path->joinAtoms('bar', 'baz\\qux');
    }

    /**
     * @dataProvider invalidPathAtomCharacterData
     */
    public function testJoinAtomsFailureAtomContainingInvalidCharacter($character)
    {
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\InvalidPathAtomCharacterException',
            sprintf(
                'Invalid path atom %s. Path atom contains invalid character %s.',
                var_export(sprintf('foo%sbar', $character), true),
                var_export($character, true)
            )
        );
        $path->joinAtoms('bar', sprintf('foo%sbar', $character));
    }

    public function testJoinAtomsFailureEmptyAtom()
    {
        $path = $this->factory->create('/foo');

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
        $path = $this->factory->create('/foo');
        $result = $path->joinAtomSequence(new ArrayIterator(array('bar', 'baz')));

        $this->assertSame('/foo/bar/baz', $result->string());
    }

    public function testJoinAtomSequenceFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz/qux'. Path atoms must not contain separators."
        );
        $path->joinAtomSequence(array('bar', 'baz/qux'));
    }

    public function testJoinAtomSequenceFailureAtomContainingBackslash()
    {
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz\\\\qux'. Path atoms must not contain separators."
        );
        $path->joinAtomSequence(array('bar', 'baz\\qux'));
    }

    /**
     * @dataProvider invalidPathAtomCharacterData
     */
    public function testJoinAtomSequenceFailureAtomContainingInvalidCharacter($character)
    {
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\InvalidPathAtomCharacterException',
            sprintf(
                'Invalid path atom %s. Path atom contains invalid character %s.',
                var_export(sprintf('foo%sbar', $character), true),
                var_export($character, true)
            )
        );
        $path->joinAtomSequence(array('bar', sprintf('foo%sbar', $character)));
    }

    public function testJoinAtomSequenceFailureEmptyAtom()
    {
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\EmptyPathAtomException'
        );
        $path->joinAtomSequence(array('bar', ''));
    }

    public function joinData()
    {
        //                                                         path           joinPath    expectedResult
        return array(
            'Single atom to root'                         => array('/',           'foo',      '/foo'),
            'Multiple atoms to root'                      => array('/',           'foo/bar',  '/foo/bar'),
            'Multiple atoms to multiple atoms'            => array('/foo/bar',    'baz/qux',  '/foo/bar/baz/qux'),
            'Whitespace atoms'                            => array('/foo',        ' ',        '/foo/ '),
            'Special atoms'                               => array('/foo',        './..',     '/foo/./..'),

            'Single atom to root with drive'              => array('C:/',         'foo',      'C:/foo'),
            'Multiple atoms to root with drive'           => array('C:/',         'foo/bar',  'C:/foo/bar'),
            'Multiple atoms to multiple atoms with drive' => array('C:/foo/bar',  'baz/qux',  'C:/foo/bar/baz/qux'),
            'Whitespace atoms with drive'                 => array('C:/foo',      ' ',        'C:/foo/ '),
            'Special atoms with drive'                    => array('C:/foo',      './..',     'C:/foo/./..'),
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
        $path = $this->factory->create('/foo');
        $joinPath = $this->factory->create('/bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $path->join($joinPath);
    }

    public function testJoinFailureAbsoluteJoinPathWithDrive()
    {
        $path = $this->factory->create('C:/foo');
        $joinPath = $this->factory->create('D:/bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $path->join($joinPath);
    }

    public function joinTrailingSlashData()
    {
        //                                                path           expectedResult
        return array(
            'Root atom'                          => array('/',           '/'),
            'Single atom'                        => array('/foo',        '/foo/'),
            'Whitespace atom'                    => array('/foo ',       '/foo /'),
            'Multiple atoms'                     => array('/foo/bar',    '/foo/bar/'),
            'Existing trailing slash'            => array('/foo/',       '/foo/'),

            'Root atom with drive'               => array('C:/',         'C:/'),
            'Single atom with drive'             => array('C:/foo',      'C:/foo/'),
            'Whitespace atom with drive'         => array('C:/foo ',     'C:/foo /'),
            'Multiple atoms with drive'          => array('C:/foo/bar',  'C:/foo/bar/'),
            'Existing trailing slash with drive' => array('C:/foo/',     'C:/foo/'),
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
        //                                                                path        extensions            expectedResult
        return array(
            'Add to root'                                        => array('/',        array('foo'),         '/.foo'),
            'Empty extension'                                    => array('/foo',     array(''),            '/foo.'),
            'Whitespace extension'                               => array('/foo',     array(' '),           '/foo. '),
            'Single extension'                                   => array('/foo',     array('bar'),         '/foo.bar'),
            'Multiple extensions'                                => array('/foo',     array('bar', 'baz'),  '/foo.bar.baz'),
            'Empty extension with trailing slash'                => array('/foo/',    array(''),            '/foo.'),
            'Multiple extensions with trailing slash'            => array('/foo/',    array('bar', 'baz'),  '/foo.bar.baz'),

            'Add to root with drive'                             => array('C:/',      array('foo'),         'C:/.foo'),
            'Empty extension with drive'                         => array('C:/foo',   array(''),            'C:/foo.'),
            'Whitespace extension with drive'                    => array('C:/foo',   array(' '),           'C:/foo. '),
            'Single extension with drive'                        => array('C:/foo',   array('bar'),         'C:/foo.bar'),
            'Multiple extensions with drive'                     => array('C:/foo',   array('bar', 'baz'),  'C:/foo.bar.baz'),
            'Empty extension with trailing slash with drive'     => array('C:/foo/',  array(''),            'C:/foo.'),
            'Multiple extensions with trailing slash with drive' => array('C:/foo/',  array('bar', 'baz'),  'C:/foo.bar.baz'),
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
        $path = $this->factory->create('/foo');

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

    public function testJoinExtensionSequenWithNonArray()
    {
        $path = $this->factory->create('/foo');
        $result = $path->joinExtensionSequence(new ArrayIterator(array('bar', 'baz')));

        $this->assertSame('/foo.bar.baz', $result->string());
    }

    public function testJoinExtensionSequenceFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo.bar/baz'. Path atoms must not contain separators."
        );
        $path->joinExtensionSequence(array('bar/baz'));
    }

    public function suffixNameData()
    {
        //                                                            path            suffix       expectedResult
        return array(
            'Root'                                           => array('/',            'foo',       '/foo'),
            'Empty suffix'                                   => array('/foo/bar',     '',          '/foo/bar'),
            'Empty suffix and trailing slash'                => array('/foo/bar/',    '',          '/foo/bar'),
            'Whitespace suffix'                              => array('/foo/bar',     ' ',         '/foo/bar '),
            'Normal suffix'                                  => array('/foo/bar',     '-baz',      '/foo/bar-baz'),
            'Suffix with dots'                               => array('/foo/bar',     '.baz.qux',  '/foo/bar.baz.qux'),
            'Suffix with dots and trailing slash'            => array('/foo/bar/',    '.baz.qux',  '/foo/bar.baz.qux'),

            'Root with drive'                                => array('C:/',          'foo',       'C:/foo'),
            'Empty suffix with drive'                        => array('C:/foo/bar',   '',          'C:/foo/bar'),
            'Empty suffix and trailing slash with drive'     => array('C:/foo/bar/',  '',          'C:/foo/bar'),
            'Whitespace suffix with drive'                   => array('C:/foo/bar',   ' ',         'C:/foo/bar '),
            'Normal suffix with drive'                       => array('C:/foo/bar',   '-baz',      'C:/foo/bar-baz'),
            'Suffix with dots with drive'                    => array('C:/foo/bar',   '.baz.qux',  'C:/foo/bar.baz.qux'),
            'Suffix with dots and trailing slash with drive' => array('C:/foo/bar/',  '.baz.qux',  'C:/foo/bar.baz.qux'),
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
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo/bar'. Path atoms must not contain separators."
        );
        $path->suffixName('/bar');
    }

    public function prefixNameData()
    {
        //                                                             path          prefix         expectedResult
        return array(
            'Root'                                            => array('/',          'foo',         '/foo'),
            'Empty prefix'                                    => array('/foo/bar',   '',            '/foo/bar'),
            'Empty prefix and trailing slash'                 => array('/foo/bar/',  '',            '/foo/bar'),
            'Whitespace prefix'                               => array('/foo/bar',   ' ',           '/foo/ bar'),
            'Normal prefix'                                   => array('/foo/bar',   'baz-',        '/foo/baz-bar'),
            'Prefix with dots'                                => array('/foo/bar',   'baz.qux.',    '/foo/baz.qux.bar'),
            'Prefix with dots and trailing slash'             => array('/foo/bar/',  'baz.qux.',    '/foo/baz.qux.bar'),

            'Root with drive'                                 => array('C:/',          'foo',       'C:/foo'),
            'Empty prefix with drive'                         => array('C:/foo/bar',   '',          'C:/foo/bar'),
            'Empty prefix and trailing slash with drive'      => array('C:/foo/bar/',  '',          'C:/foo/bar'),
            'Whitespace prefix with drive'                    => array('C:/foo/bar',   ' ',         'C:/foo/ bar'),
            'Normal prefix with drive'                        => array('C:/foo/bar',   'baz-',      'C:/foo/baz-bar'),
            'Prefix with dots with drive'                     => array('C:/foo/bar',   'baz.qux.',  'C:/foo/baz.qux.bar'),
            'Prefix with dots and trailing slash with drive'  => array('C:/foo/bar/',  'baz.qux.',  'C:/foo/baz.qux.bar'),
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
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'bar/foo'. Path atoms must not contain separators."
        );
        $path->prefixName('bar/');
    }

    public function replaceData()
    {
        //                                                         path                   offset  replacement              length  expectedResult
        return array(
            'Replace single atom implicit'                => array('/foo/bar/baz/qux',    2,      array('doom'),           null,   '/foo/bar/doom'),
            'Replace multiple atoms implicit'             => array('/foo/bar/baz/qux',    1,      array('doom', 'splat'),  null,   '/foo/doom/splat'),
            'Replace single atom explicit'                => array('/foo/bar/baz/qux',    1,      array('doom'),           2,      '/foo/doom/qux'),
            'Replace multiple atoms explicit'             => array('/foo/bar/baz/qux',    1,      array('doom', 'splat'),  1,      '/foo/doom/splat/baz/qux'),
            'Replace atoms past end'                      => array('/foo/bar/baz/qux',    111,    array('doom'),           222,    '/foo/bar/baz/qux/doom'),

            'Replace single atom implicit with drive'     => array('C:/foo/bar/baz/qux',  2,      array('doom'),           null,   'C:/foo/bar/doom'),
            'Replace multiple atoms implicit with drive'  => array('C:/foo/bar/baz/qux',  1,      array('doom', 'splat'),  null,   'C:/foo/doom/splat'),
            'Replace single atom explicit with drive'     => array('C:/foo/bar/baz/qux',  1,      array('doom'),           2,      'C:/foo/doom/qux'),
            'Replace multiple atoms explicit with drive'  => array('C:/foo/bar/baz/qux',  1,      array('doom', 'splat'),  1,      'C:/foo/doom/splat/baz/qux'),
            'Replace atoms past end with drive'           => array('C:/foo/bar/baz/qux',  111,    array('doom'),           222,    'C:/foo/bar/baz/qux/doom'),
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
        $path = $this->factory->create('/foo/bar/baz/qux');
        $result = $path->replace(1, new ArrayIterator(array('doom', 'splat')), 1);

        $this->assertSame('/foo/doom/splat/baz/qux', $result->string());
    }

    public function testReplaceFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'bar/'. Path atoms must not contain separators."
        );
        $path->replace(1, array('bar/'));
    }

    public function testReplaceFailureAtomContainingBackslash()
    {
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'bar\\\\'. Path atoms must not contain separators."
        );
        $path->replace(1, array('bar\\'));
    }

    public function replaceNameData()
    {
        //                                                        path               name         expectedResult
        return array(
            'Root'                                       => array('/',               'foo',       '/foo'),
            'Empty name'                                 => array('/foo/bar',        '',          '/foo'),
            'Empty name with trailing slash'             => array('/foo/bar/',       '',          '/foo'),
            'Whitespace name'                            => array('/foo/bar',        ' ',         '/foo/ '),
            'Normal name'                                => array('/foo.bar.baz',    'qux',       '/qux'),
            'Normal name with extensions'                => array('/foo.bar.baz',    'qux.doom',  '/qux.doom'),

            'Root with drive'                            => array('C:/',             'foo',       'C:/foo'),
            'Empty name with drive'                      => array('C:/foo/bar',      '',          'C:/foo'),
            'Empty name with trailing slash with drive'  => array('C:/foo/bar/',     '',          'C:/foo'),
            'Whitespace name with drive'                 => array('C:/foo/bar',      ' ',         'C:/foo/ '),
            'Normal name with drive'                     => array('C:/foo.bar.baz',  'qux',       'C:/qux'),
            'Normal name with extensions with drive'     => array('C:/foo.bar.baz',  'qux.doom',  'C:/qux.doom'),
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
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'bar/'. Path atoms must not contain separators."
        );
        $path->replaceName('bar/');
    }

    public function replaceNameWithoutExtensionData()
    {
        //                                                        path               name         expectedResult
        return array(
            'Root'                                       => array('/',               'foo',       '/foo'),
            'Empty name'                                 => array('/foo/bar',        '',          '/foo'),
            'Empty name with trailing slash'             => array('/foo/bar/',       '',          '/foo'),
            'Whitespace name'                            => array('/foo/bar',        ' ',         '/foo/ '),
            'Normal name'                                => array('/foo.bar.baz',    'qux',       '/qux.baz'),
            'Normal name with extensions'                => array('/foo.bar.baz',    'qux.doom',  '/qux.doom.baz'),

            'Root with drive'                            => array('C:/',             'foo',       'C:/foo'),
            'Empty name with drive'                      => array('C:/foo/bar',      '',          'C:/foo'),
            'Empty name with trailing slash with drive'  => array('C:/foo/bar/',     '',          'C:/foo'),
            'Whitespace name with drive'                 => array('C:/foo/bar',      ' ',         'C:/foo/ '),
            'Normal name with drive'                     => array('C:/foo.bar.baz',  'qux',       'C:/qux.baz'),
            'Normal name with extensions with drive'     => array('C:/foo.bar.baz',  'qux.doom',  'C:/qux.doom.baz'),
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
        $path = $this->factory->create('/foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'qux/.baz'. Path atoms must not contain separators."
        );
        $path->replaceNameWithoutExtension('qux/');
    }

    public function replaceNamePrefixData()
    {
        //                                                        path               name         expectedResult
        return array(
            'Root'                                       => array('/',               'foo',       '/foo'),
            'Empty name'                                 => array('/foo/bar',        '',          '/foo'),
            'Empty name with trailing slash'             => array('/foo/bar/',       '',          '/foo'),
            'Whitespace name'                            => array('/foo/bar',        ' ',         '/foo/ '),
            'Normal name'                                => array('/foo.bar.baz',    'qux',       '/qux.bar.baz'),
            'Normal name with extensions'                => array('/foo.bar.baz',    'qux.doom',  '/qux.doom.bar.baz'),

            'Root with drive'                            => array('C:/',             'foo',       'C:/foo'),
            'Empty name with drive'                      => array('C:/foo/bar',      '',          'C:/foo'),
            'Empty name with trailing slash with drive'  => array('C:/foo/bar/',     '',          'C:/foo'),
            'Whitespace name with drive'                 => array('C:/foo/bar',      ' ',         'C:/foo/ '),
            'Normal name with drive'                     => array('C:/foo.bar.baz',  'qux',       'C:/qux.bar.baz'),
            'Normal name with extensions with drive'     => array('C:/foo.bar.baz',  'qux.doom',  'C:/qux.doom.bar.baz'),
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
        $path = $this->factory->create('/foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'qux/.bar.baz'. Path atoms must not contain separators."
        );
        $path->replaceNamePrefix('qux/');
    }

    public function replaceNameSuffixData()
    {
        //                                                        path               name         expectedResult
        return array(
            'Root'                                       => array('/',               'foo',       '/.foo'),
            'Empty name'                                 => array('/foo/bar',        '',          '/foo/bar.'),
            'Empty name with trailing slash'             => array('/foo/bar/',       '',          '/foo/bar.'),
            'Whitespace name'                            => array('/foo/bar',        ' ',         '/foo/bar. '),
            'Normal name'                                => array('/foo.bar.baz',    'qux',       '/foo.qux'),
            'Normal name with extensions'                => array('/foo.bar.baz',    'qux.doom',  '/foo.qux.doom'),

            'Root with drive'                            => array('C:/',             'foo',       'C:/.foo'),
            'Empty name with drive'                      => array('C:/foo/bar',      '',          'C:/foo/bar.'),
            'Empty name with trailing slash with drive'  => array('C:/foo/bar/',     '',          'C:/foo/bar.'),
            'Whitespace name with drive'                 => array('C:/foo/bar',      ' ',         'C:/foo/bar. '),
            'Normal name with drive'                     => array('C:/foo.bar.baz',  'qux',       'C:/foo.qux'),
            'Normal name with extensions with drive'     => array('C:/foo.bar.baz',  'qux.doom',  'C:/foo.qux.doom'),
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
        $path = $this->factory->create('/foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo.qux/'. Path atoms must not contain separators."
        );
        $path->replaceNameSuffix('qux/');
    }

    public function replaceExtensionData()
    {
        //                                                        path               name         expectedResult
        return array(
            'Root'                                       => array('/',               'foo',       '/.foo'),
            'Empty name'                                 => array('/foo/bar',        '',          '/foo/bar.'),
            'Empty name with trailing slash'             => array('/foo/bar/',       '',          '/foo/bar.'),
            'Whitespace name'                            => array('/foo/bar',        ' ',         '/foo/bar. '),
            'Normal name'                                => array('/foo.bar.baz',    'qux',       '/foo.bar.qux'),
            'Normal name with extensions'                => array('/foo.bar.baz',    'qux.doom',  '/foo.bar.qux.doom'),

            'Root with drive'                            => array('C:/',             'foo',       'C:/.foo'),
            'Empty name with drive'                      => array('C:/foo/bar',      '',          'C:/foo/bar.'),
            'Empty name with trailing slash with drive'  => array('C:/foo/bar/',     '',          'C:/foo/bar.'),
            'Whitespace name with drive'                 => array('C:/foo/bar',      ' ',         'C:/foo/bar. '),
            'Normal name with drive'                     => array('C:/foo.bar.baz',  'qux',       'C:/foo.bar.qux'),
            'Normal name with extensions with drive'     => array('C:/foo.bar.baz',  'qux.doom',  'C:/foo.bar.qux.doom'),
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
        $path = $this->factory->create('/foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo.bar.qux/'. Path atoms must not contain separators."
        );
        $path->replaceExtension('qux/');
    }

    public function testNormalize()
    {
        $path = $this->factory->create('/foo/../bar');
        $normalizedPath = $this->factory->create('/bar');

        $this->assertEquals($normalizedPath, $path->normalize());
    }

    public function testNormalizeWithDrive()
    {
        $path = $this->factory->create('C:/foo/../bar');
        $normalizedPath = $this->factory->create('C:/bar');

        $this->assertEquals($normalizedPath, $path->normalize());
    }

    public function testNormalizeCustomNormalizer()
    {
        $path = $this->factory->create('/foo/../bar');
        $normalizedPath = $this->factory->create('/bar');
        $normalizer = Phake::mock('Eloquent\Pathogen\Normalizer\PathNormalizerInterface');
        Phake::when($normalizer)->normalize($path)->thenReturn($normalizedPath);

        $this->assertSame($normalizedPath, $path->normalize($normalizer));
    }

    // tests for AbsolutePathInterface implementation ==========================

    public function rootData()
    {
        //                                             path          isRoot
        return array(
            'Root'                            => array('/',          true),
            'Root non-normalized'             => array('/foo/..',    true),
            'Not root'                        => array('/foo',       false),

            'Root with drive'                 => array('C:/',        true),
            'Root non-normalized with drive'  => array('C:/foo/..',  true),
            'Not root with drive'             => array('C:/foo',     false),
        );
    }

    /**
     * @dataProvider rootData
     */
    public function testIsRoot($pathString, $isRoot)
    {
        $this->assertSame($isRoot, $this->factory->create($pathString)->isRoot());
    }

    public function ancestryData()
    {
        //                                                  parent                child                        isParentOf  isAncestorOf
        return array(
            'Parent'                               => array('/foo',               '/foo/bar',                  true,       true),
            'Root as parent'                       => array('/',                  '/foo',                      true,       true),
            'Resolve special atoms'                => array('/foo/bar/../baz',    '/foo/./baz/qux/../doom',    true,       true),
            'Not immediate parent'                 => array('/foo',               '/foo/bar/baz',              false,      true),
            'Root not immediate parent'            => array('/',                  '/foo/bar',                  false,      true),
            'Unrelated paths'                      => array('/foo',               '/bar',                      false,      false),
            'Same paths'                           => array('/foo/bar',           '/foor/bar',                 false,      false),
            'Longer parent path'                   => array('/foo/bar/baz',       '/foo',                      false,      false),

            'Parent with drive'                    => array('C:/foo',             'C:/foo/bar',                true,       true),
            'Root as parent with drive'            => array('C:/',                'C:/foo',                    true,       true),
            'Resolve special atoms with drive'     => array('C:/foo/bar/../baz',  'C:/foo/./baz/qux/../doom',  true,       true),
            'Not immediate parent with drive'      => array('C:/foo',             'C:/foo/bar/baz',            false,      true),
            'Root not immediate parent with drive' => array('C:/',                'C:/foo/bar',                false,      true),
            'Unrelated paths with drive'           => array('C:/foo',             'C:/bar',                    false,      false),
            'Same paths with drive'                => array('C:/foo/bar',         'C:/foor/bar',               false,      false),
            'Longer parent path with drive'        => array('C:/foo/bar/baz',     'C:/foo',                    false,      false),
            'Parent with mismatched drive'         => array('C:/foo',             'D:/foo/bar',                false,      false),
            'Parent with drive ignore case'        => array('C:/foo',             'c:/foo/bar',                true,       true),
        );
    }

    /**
     * @dataProvider ancestryData
     */
    public function testAncestry($parentString, $childString, $isParentOf, $isAncestorOf)
    {
        $parent = $this->factory->create($parentString);
        $child = $this->factory->create($childString);

        $this->assertSame($isParentOf, $parent->isParentOf($child));
        $this->assertSame($isAncestorOf, $parent->isAncestorOf($child));
    }

    public function testAncestryWithRegularPath()
    {
        $parent = $this->factory->create('/foo');
        $child = $this->regularPathFactory->create('/foo/bar');

        $this->assertTrue($parent->isParentOf($child));
        $this->assertTrue($parent->isAncestorOf($child));
    }

    public function testAncestryWithRegularPathMismatch()
    {
        $parent = $this->factory->create('C:/foo');
        $child = $this->regularPathFactory->create('/foo/bar');

        $this->assertFalse($parent->isParentOf($child));
        $this->assertFalse($parent->isAncestorOf($child));
    }

    public function testIsParentOfFailureRelativeChild()
    {
        $parent = $this->factory->create('/foo');
        $child = $this->factory->create('foo/bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $parent->isParentOf($child);
    }

    public function testIsAncestorOfFailureRelativeChild()
    {
        $parent = $this->factory->create('/foo');
        $child = $this->factory->create('foo/bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $parent->isAncestorOf($child);
    }

    public function relativeToData()
    {
        //                                                  parent              child               expectedResult
        return array(
            'Self'                                 => array('/foo',             '/foo',             '.'),
            'Child'                                => array('/foo',             '/foo/bar',         'bar'),
            'Ancestor'                             => array('/foo',             '/foo/bar/baz',     'bar/baz'),
            'Sibling'                              => array('/foo',             '/bar',             '../bar'),
            'Parent\'s sibling'                    => array('/foo/bar/baz',     '/foo/qux',         '../../qux'),
            'Parent\'s sibling\'s child'           => array('/foo/bar/baz',     '/foo/qux/doom',    '../../qux/doom'),
            'Completely unrelated'                 => array('/foo/bar/baz',     '/qux/doom',        '../../../qux/doom'),
            'Lengthly unrelated child'             => array('/foo/bar',         '/baz/qux/doom',    '../../baz/qux/doom'),

            'Self with drive'                       => array('C:/foo',          'C:/foo',           '.'),
            'Child with drive'                      => array('C:/foo',          'C:/foo/bar',       'bar'),
            'Ancestor with drive'                   => array('C:/foo',          'C:/foo/bar/baz',   'bar/baz'),
            'Sibling with drive'                    => array('C:/foo',          'C:/bar',           '../bar'),
            'Parent\'s sibling with drive'          => array('C:/foo/bar/baz',  'C:/foo/qux',       '../../qux'),
            'Parent\'s sibling\'s child with drive' => array('C:/foo/bar/baz',  'C:/foo/qux/doom',  '../../qux/doom'),
            'Completely unrelated with drive'       => array('C:/foo/bar/baz',  'C:/qux/doom',      '../../../qux/doom'),
            'Lengthly unrelated child with drive'   => array('C:/foo/bar',      'C:/baz/qux/doom',  '../../baz/qux/doom'),
            'Child with drive ignore case'          => array('C:/foo',          'c:/foo/bar',       'bar'),
        );
    }

    /**
     * @dataProvider relativeToData
     */
    public function testRelativeTo($parentString, $childString, $expectedResultString)
    {
        $parent = $this->factory->create($parentString);
        $child = $this->factory->create($childString);
        $result = $child->relativeTo($parent);

        $this->assertSame($expectedResultString, $result->string());
    }

    public function testRelativeToWithRegularPath()
    {
        $parent = $this->factory->create('/foo');
        $child = $this->regularPathFactory->create('/foo/bar');
        $result = $child->relativeTo($parent);

        $this->assertSame('bar', $result->string());
    }

    public function testRelativeToFailureDriveMismatch()
    {
        $child = $this->factory->create('C:/foo/bar');
        $parent = $this->factory->create('D:/foo');

        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\DriveMismatchException',
            "Drive specifiers 'C' and 'D' do not match."
        );
        $child->relativeTo($parent);
    }

    public function testRelativeToFailureDriveMismatchRegularPath()
    {
        $child = $this->factory->create('C:/foo/bar');
        $parent = $this->regularPathFactory->create('/foo');

        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\DriveMismatchException',
            "Drive specifiers 'C' and NULL do not match."
        );
        $child->relativeTo($parent);
    }

    public function replaceNameAtomsData()
    {
        //                                                        path                    offset  replacement              length  expectedResult
        return array(
            'Replace single atom implicit'               => array('/foo.bar.baz.qux',     2,      array('doom'),           null,   '/foo.bar.doom'),
            'Replace multiple atoms implicit'            => array('/foo.bar.baz.qux',     1,      array('doom', 'splat'),  null,   '/foo.doom.splat'),
            'Replace single atom explicit'               => array('/foo.bar.baz.qux',     1,      array('doom'),           2,      '/foo.doom.qux'),
            'Replace multiple atoms explicit'            => array('/foo.bar.baz.qux',     1,      array('doom', 'splat'),  1,      '/foo.doom.splat.baz.qux'),
            'Replace atoms past end'                     => array('/foo.bar.baz.qux',     111,    array('doom'),           222,    '/foo.bar.baz.qux.doom'),

            'Replace single atom implicit with drive'     => array('C:/foo.bar.baz.qux',  2,      array('doom'),           null,   'C:/foo.bar.doom'),
            'Replace multiple atoms implicit with drive'  => array('C:/foo.bar.baz.qux',  1,      array('doom', 'splat'),  null,   'C:/foo.doom.splat'),
            'Replace single atom explicit with drive'     => array('C:/foo.bar.baz.qux',  1,      array('doom'),           2,      'C:/foo.doom.qux'),
            'Replace multiple atoms explicit with drive'  => array('C:/foo.bar.baz.qux',  1,      array('doom', 'splat'),  1,      'C:/foo.doom.splat.baz.qux'),
            'Replace atoms past end with drive'           => array('C:/foo.bar.baz.qux',  111,    array('doom'),           222,    'C:/foo.bar.baz.qux.doom'),
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
        $path = $this->factory->create('/foo.bar.baz.qux');
        $result = $path->replaceNameAtoms(1, new ArrayIterator(array('doom', 'splat')), 1);

        $this->assertSame('/foo.doom.splat.baz.qux', $result->string());
    }
}
