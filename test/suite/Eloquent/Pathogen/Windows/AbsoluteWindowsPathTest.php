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
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Pathogen\Windows\AbsoluteWindowsPath
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

    public function pathData()
    {
        //                            path                      drive atoms                            hasTrailingSeparator
        return array(
            'Root'           => array('C:/',                    'C',  array(),                         false),
            'Single atom'    => array('C:/foo',                 'C',  array('foo'),                    false),
            'Trailing slash' => array('C:/foo/',                'C',  array('foo'),                    true),
            'Multiple atoms' => array('C:/foo/bar',             'C',  array('foo', 'bar'),             false),
            'Parent atom'    => array('C:/foo/../bar',          'C',  array('foo', '..', 'bar'),       false),
            'Self atom'      => array('C:/foo/./bar',           'C',  array('foo', '.', 'bar'),        false),
            'Whitespace'     => array('C:/ foo bar / baz qux ', 'C',  array(' foo bar ', ' baz qux '), false),
        );
    }

    /**
     * @dataProvider pathData
     */
    public function testConstructor($pathString, $drive, array $atoms, $hasTrailingSeparator)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($atoms, $path->atoms());
        $this->assertTrue($path->hasDrive());
        $this->assertSame($drive, $path->drive());
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
        $this->assertSame($pathString, $path->string());
        $this->assertSame($pathString, strval($path));
    }

    public function testConstructorDefaults()
    {
        $this->path = new AbsoluteWindowsPath('C', array());

        $this->assertFalse($this->path->hasTrailingSeparator());
    }

    public function testConstructorFailureAtomContainingSeparator()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo/bar'. Path atoms must not contain separators."
        );
        new AbsoluteWindowsPath('C', array('foo/bar'));
    }

    public function testConstructorFailureAtomContainingBackslash()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo\\\\bar'. Path atoms must not contain separators."
        );
        new AbsoluteWindowsPath('C', array('foo\bar'));
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
        new AbsoluteWindowsPath('C', array(sprintf('foo%sbar', $character)));
    }

    public function testConstructorFailureEmptyAtom()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\EmptyPathAtomException'
        );
        new AbsoluteWindowsPath('C', array(''));
    }

    public function testConstructorFailureInvalidDriveSpecifierCharacter()
    {
        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\InvalidDriveSpecifierException'
        );
        new AbsoluteWindowsPath('$', array());
    }

    public function testConstructorFailureInvalidDriveSpecifierEmpty()
    {
        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\InvalidDriveSpecifierException'
        );
        new AbsoluteWindowsPath('', array());
    }

    public function testConstructorFailureInvalidDriveSpecifierLength()
    {
        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\InvalidDriveSpecifierException'
        );
        new AbsoluteWindowsPath('CC', array());
    }

    // Implementation of WindowsPathInterface ==================================

    public function testMatchesDrive()
    {
        $path = $this->factory->create('C:/foo/bar');

        $this->assertTrue($path->matchesDrive('C'));
        $this->assertTrue($path->matchesDrive('c'));
        $this->assertFalse($path->matchesDrive('X'));
        $this->assertFalse($path->matchesDrive(null));
    }

    public function testMatchesDriveOrNull()
    {
        $path = $this->factory->create('C:/foo/bar');

        $this->assertTrue($path->matchesDriveOrNull('C'));
        $this->assertTrue($path->matchesDriveOrNull('c'));
        $this->assertFalse($path->matchesDriveOrNull('X'));
        $this->assertTrue($path->matchesDriveOrNull(null));
    }

    public function testJoinDrive()
    {
        $path = $this->factory->create('C:/foo/bar');
        $joinedToDrive = $path->joinDrive('X');
        $joinedToNull = $path->joinDrive(null);

        $this->assertTrue($joinedToDrive instanceof AbsoluteWindowsPath);
        $this->assertSame('X:/foo/bar', $joinedToDrive->string());
        $this->assertTrue($joinedToNull instanceof RelativeWindowsPath);
        $this->assertSame('/foo/bar', $joinedToNull->string());
    }

    // tests for PathInterface implementation ==================================

    public function testAtomAt()
    {
        $path = $this->factory->create('C:/foo/bar');

        $this->assertSame('foo', $path->atomAt(0));
        $this->assertSame('bar', $path->atomAt(1));
        $this->assertSame('bar', $path->atomAt(-1));
        $this->assertSame('foo', $path->atomAt(-2));
    }

    public function testAtomAtFailure()
    {
        $path = $this->factory->create('C:/foo/bar');

        $this->setExpectedException('Eloquent\Pathogen\Exception\UndefinedAtomException');
        $path->atomAt(2);
    }

    public function testAtomAtDefault()
    {
        $path = $this->factory->create('C:/foo/bar');

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
        //                                  path                   index  length  expectedResult
        return array(
            'Slice till end'       => array('C:/foo/bar/baz/qux',  1,     null,   array('bar', 'baz', 'qux')),
            'Slice specific range' => array('C:/foo/bar/baz/qux',  1,     2,      array('bar', 'baz')),
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
        //                                             path               name            nameWithoutExtension  namePrefix  nameSuffix  extension
        return array(
            'Root'                            => array('C:/',             '',             '',                   '',         null,       null),
            'No extensions'                   => array('C:/foo',          'foo',          'foo',                'foo',      null,       null),
            'Empty extension'                 => array('C:/foo.',         'foo.',         'foo',                'foo',      '',         ''),
            'Whitespace extension'            => array('C:/foo. ',        'foo. ',        'foo',                'foo',      ' ',        ' '),
            'Single extension'                => array('C:/foo.bar',      'foo.bar',      'foo',                'foo',      'bar',      'bar'),
            'Multiple extensions'             => array('C:/foo.bar.baz',  'foo.bar.baz',  'foo.bar',            'foo',      'bar.baz',  'baz'),
            'No name with single extension'   => array('C:/.foo',         '.foo',         '',                   '',         'foo',      'foo'),
            'No name with multiple extension' => array('C:/.foo.bar',     '.foo.bar',     '.foo',               '',         'foo.bar',  'bar'),
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
        //                                  path           nameAtoms
        return array(
            'Root'                 => array('C:/',         array('')),
            'Root with self'       => array('C:/.',        array('', '')),
            'Single name atom'     => array('C:/foo',      array('foo')),
            'Multiple name atoms'  => array('C:/foo.bar',  array('foo', 'bar')),
            'Multiple path atoms'  => array('C:/foo/bar',  array('bar')),
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
        $path = $this->factory->create('C:/foo.bar');

        $this->assertSame('foo', $path->nameAtomAt(0));
        $this->assertSame('bar', $path->nameAtomAt(1));
        $this->assertSame('bar', $path->nameAtomAt(-1));
        $this->assertSame('foo', $path->nameAtomAt(-2));
    }

    public function testNameAtomAtFailure()
    {
        $path = $this->factory->create('C:/foo.bar');

        $this->setExpectedException('Eloquent\Pathogen\Exception\UndefinedAtomException');
        $path->nameAtomAt(2);
    }

    public function testNameAtomAtDefault()
    {
        $path = $this->factory->create('C:/foo.bar');

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
        //                                  path                   index  length  expectedResult
        return array(
            'Slice till end'       => array('C:/foo.bar.baz.qux',  1,     null,   array('bar', 'baz', 'qux')),
            'Slice specific range' => array('C:/foo.bar.baz.qux',  1,     2,      array('bar', 'baz')),
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
        //                                       path                   needle         caseSensitive  expectedResult
        return array(
            'Empty'                     => array('C:/',                 '',            null,          true),
            'Prefix'                    => array('C:/foo/bar/baz.qux',  'c:/FOO/BAR',  null,          true),
            'Middle'                    => array('C:/foo/bar/baz.qux',  'BAR/BAZ',     null,          true),
            'Suffix'                    => array('C:/foo/bar/baz.qux',  '/BAZ.QUX',    null,          true),
            'Not found'                 => array('C:/foo/bar/baz.qux',  'DOOM',        null,          false),

            'Empty case sensitive'      => array('C:/',                 '',            true,          true),
            'Prefix case sensitive'     => array('C:/foo/bar/baz.qux',  'C:/foo/bar',  true,          true),
            'Middle case sensitive'     => array('C:/foo/bar/baz.qux',  'bar/baz',     true,          true),
            'Suffix case sensitive'     => array('C:/foo/bar/baz.qux',  '/baz.qux',    true,          true),
            'Not found case sensitive'  => array('C:/foo/bar/baz.qux',  'FOO',         true,          false),
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
        //                                       path                   needle         caseSensitive  expectedResult
        return array(
            'Empty'                     => array('C:/',                 '',            null,          true),
            'Prefix'                    => array('C:/foo/bar/baz.qux',  'c:/FOO/BAR',  null,          true),
            'Middle'                    => array('C:/foo/bar/baz.qux',  'BAR/BAZ',     null,          false),
            'Suffix'                    => array('C:/foo/bar/baz.qux',  '/BAZ.QUX',    null,          false),
            'Not found'                 => array('C:/foo/bar/baz.qux',  'DOOM',        null,          false),

            'Empty case sensitive'      => array('C:/',                 '',            true,          true),
            'Prefix case sensitive'     => array('C:/foo/bar/baz.qux',  'C:/foo/bar',  true,          true),
            'Middle case sensitive'     => array('C:/foo/bar/baz.qux',  'bar/baz',     true,          false),
            'Suffix case sensitive'     => array('C:/foo/bar/baz.qux',  '/baz.qux',    true,          false),
            'Not found case sensitive'  => array('C:/foo/bar/baz.qux',  'FOO',         true,          false),
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
        //                                       path                   needle         caseSensitive  expectedResult
        return array(
            'Empty'                     => array('C:/',                 '',            null,          true),
            'Prefix'                    => array('C:/foo/bar/baz.qux',  'c:/FOO/BAR',  null,          false),
            'Middle'                    => array('C:/foo/bar/baz.qux',  'BAR/BAZ',     null,          false),
            'Suffix'                    => array('C:/foo/bar/baz.qux',  '/BAZ.QUX',    null,          true),
            'Not found'                 => array('C:/foo/bar/baz.qux',  'DOOM',        null,          false),

            'Empty case sensitive'      => array('C:/',                 '',            true,          true),
            'Prefix case sensitive'     => array('C:/foo/bar/baz.qux',  'C:/foo/bar',  true,          false),
            'Middle case sensitive'     => array('C:/foo/bar/baz.qux',  'bar/baz',     true,          false),
            'Suffix case sensitive'     => array('C:/foo/bar/baz.qux',  '/baz.qux',    true,          true),
            'Not found case sensitive'  => array('C:/foo/bar/baz.qux',  'FOO',         true,          false),
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
        //                                         path                   pattern          caseSensitive  flags          expectedResult
        return array(
            'Prefix'                      => array('C:/foo/bar/baz.qux',  'C:/FOO/BAR*',   null,          null,          true),
            'Middle'                      => array('C:/foo/bar/baz.qux',  '*BAR/BAZ*',     null,          null,          true),
            'Suffix'                      => array('C:/foo/bar/baz.qux',  '*/BAZ.QUX',     null,          null,          true),
            'Surrounding'                 => array('C:/foo/bar/baz.qux',  'C:/FOO*.QUX',   null,          null,          true),
            'Single character'            => array('C:/foo/bar/baz.qux',  '*B?R*',         null,          null,          true),
            'Single character no match'   => array('C:/foo/bar/baz.qux',  '*F?X*',         null,          null,          false),
            'Set'                         => array('C:/foo/bar/baz.qux',  '*BA[RZ]*',      null,          null,          true),
            'Set no match'                => array('C:/foo/bar/baz.qux',  '*BA[X]*',       null,          null,          false),
            'Negated set'                 => array('C:/foo/bar/baz.qux',  '*BA[!RX]*',     null,          null,          true),
            'Negated set no match'        => array('C:/foo/bar/baz.qux',  '*BA[!RZ]*',     null,          null,          false),
            'Range'                       => array('C:/foo/bar/baz.qux',  '*BA[A-R]*',     null,          null,          true),
            'Range no match'              => array('C:/foo/bar/baz.qux',  '*BA[S-Y]*',     null,          null,          false),
            'Negated range'               => array('C:/foo/bar/baz.qux',  '*BA[!S-Y]*',    null,          null,          true),
            'Negated range no match'      => array('C:/foo/bar/baz.qux',  '*BA[!R-Z]*',    null,          null,          false),
            'No partial match'            => array('C:/foo/bar/baz.qux',  'BAR',           null,          null,          false),
            'Not found'                   => array('C:/foo/bar/baz.qux',  'DOOM',          null,          null,          false),

            'Case sensitive'              => array('C:/foo/bar/baz.qux',  '*bar/baz*',     true,          null,          true),
            'Case sensitive no match'     => array('C:/foo/bar/baz.qux',  '*FOO*',         true,          null,          false),
            'Special flags'               => array('C:/foo/bar/baz.qux',  'C:/FOO/BAR/*',  false,         FNM_PATHNAME,  true),
            'Special flags no match'      => array('C:/foo/bar/baz.qux',  '*FOO/BAR*',     false,         FNM_PATHNAME,  false),
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
        //                                   path                   pattern              matches                                                  flags                 offset  expectedResult
        return array(
            'Match'                 => array('C:/foo/bar/baz.qux',  '{.*(FOO)/BAR.*}i',  array('C:/foo/bar/baz.qux', 'foo'),                      null,                 null,   true),
            'No match'              => array('C:/foo/bar/baz.qux',  '{.*DOOM.*}i',       array(),                                                 null,                 null,   false),
            'Special flags'         => array('C:/foo/bar/baz.qux',  '{.*(FOO)/BAR.*}i',  array(array('C:/foo/bar/baz.qux', 0), array('foo', 3)),  PREG_OFFSET_CAPTURE,  null,   true),
            'Match with offset'     => array('C:/foo/bar/baz.qux',  '{FOO}i',            array('foo'),                                            null,                 3,      true),
            'No match with offset'  => array('C:/foo/bar/baz.qux',  '{FOO}i',            array(),                                                 null,                 5,      false),
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
        //                                       path                   needle      caseSensitive  expectedResult
        return array(
            'Empty'                     => array('C:/',                 '',         null,          true),
            'Prefix'                    => array('C:/foo/bar.baz.qux',  'BAR.BAZ',  null,          true),
            'Middle'                    => array('C:/foo/bar.baz.qux',  'BAZ',      null,          true),
            'Suffix'                    => array('C:/foo/bar.baz.qux',  'BAZ.QUX',  null,          true),
            'Not found'                 => array('C:/foo/bar.baz.qux',  'DOOM',     null,          false),
            'Match only in name'        => array('C:/foo/bar.baz.qux',  'foo',      null,          false),

            'Empty case sensitive'      => array('C:/',                 '',         true,          true),
            'Prefix case sensitive'     => array('C:/foo/bar.baz.qux',  'bar.baz',  true,          true),
            'Middle case sensitive'     => array('C:/foo/bar.baz.qux',  'baz',      true,          true),
            'Suffix case sensitive'     => array('C:/foo/bar.baz.qux',  'baz.qux',  true,          true),
            'Not found case sensitive'  => array('C:/foo/bar.baz.qux',  'BAR',      true,          false),
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
        //                                       path                   needle      caseSensitive  expectedResult
        return array(
            'Empty'                     => array('C:/',                 '',         null,          true),
            'Prefix'                    => array('C:/foo/bar.baz.qux',  'BAR.BAZ',  null,          true),
            'Middle'                    => array('C:/foo/bar.baz.qux',  'BAZ',      null,          false),
            'Suffix'                    => array('C:/foo/bar.baz.qux',  'BAZ.QUX',  null,          false),
            'Not found'                 => array('C:/foo/bar.baz.qux',  'DOOM',     null,          false),

            'Empty case sensitive'      => array('C:/',                 '',         true,          true),
            'Prefix case sensitive'     => array('C:/foo/bar.baz.qux',  'bar.baz',  true,          true),
            'Middle case sensitive'     => array('C:/foo/bar.baz.qux',  'baz',      true,          false),
            'Suffix case sensitive'     => array('C:/foo/bar.baz.qux',  'baz.qux',  true,          false),
            'Not found case sensitive'  => array('C:/foo/bar.baz.qux',  'BAR',      true,          false),
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
        //                                         path                   pattern        caseSensitive  flags        expectedResult
        return array(
            'Prefix'                      => array('C:/foo/bar.baz.qux',  'BAR.BAZ*',    null,          null,        true),
            'Middle'                      => array('C:/foo/bar.baz.qux',  '*BAZ*',       null,          null,        true),
            'Suffix'                      => array('C:/foo/bar.baz.qux',  '*BAZ.QUX',    null,          null,        true),
            'Surrounding'                 => array('C:/foo/bar.baz.qux',  'BAR.*.QUX',   null,          null,        true),
            'Single character'            => array('C:/foo/bar.baz.qux',  '*B?R*',       null,          null,        true),
            'Single character no match'   => array('C:/foo/bar.baz.qux',  '*B?X*',       null,          null,        false),
            'Set'                         => array('C:/foo/bar.baz.qux',  '*BA[RZ]*',    null,          null,        true),
            'Set no match'                => array('C:/foo/bar.baz.qux',  '*BA[X]*',     null,          null,        false),
            'Negated set'                 => array('C:/foo/bar.baz.qux',  '*BA[!RX]*',   null,          null,        true),
            'Negated set no match'        => array('C:/foo/bar.baz.qux',  '*BA[!RZ]*',   null,          null,        false),
            'Range'                       => array('C:/foo/bar.baz.qux',  '*BA[A-R]*',   null,          null,        true),
            'Range no match'              => array('C:/foo/bar.baz.qux',  '*BA[S-Y]*',   null,          null,        false),
            'Negated range'               => array('C:/foo/bar.baz.qux',  '*BA[!S-Y]*',  null,          null,        true),
            'Negated range no match'      => array('C:/foo/bar.baz.qux',  '*BA[!R-Z]*',  null,          null,        false),
            'No partial match'            => array('C:/foo/bar.baz.qux',  'BAZ',         null,          null,        false),
            'Not found'                   => array('C:/foo/bar.baz.qux',  'DOOM',        null,          null,        false),

            'Case sensitive'              => array('C:/foo/bar.baz.qux',  '*baz*',       true,          null,        true),
            'Case sensitive no match'     => array('C:/foo/bar.baz.qux',  '*BAZ*',       true,          null,        false),
            'Special flags'               => array('C:/foo/.bar.baz',     '.bar*',       false,         FNM_PERIOD,  true),
            'Special flags no match'      => array('C:/foo/.bar.baz',     '*bar*',       false,         FNM_PERIOD,  false),
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
        //                                   path                   pattern               matches                                           flags                 offset  expectedResult
        return array(
            'Match'                 => array('C:/foo/bar.baz.qux',  '{.*(BAR)\.BAZ.*}i',  array('bar.baz.qux', 'bar'),                      null,                 null,   true),
            'No match'              => array('C:/foo/bar.baz.qux',  '{.*DOOM.*}i',        array(),                                          null,                 null,   false),
            'Special flags'         => array('C:/foo/bar.baz.qux',  '{.*BAR\.(BAZ).*}i',  array(array('bar.baz.qux', 0), array('baz', 4)),  PREG_OFFSET_CAPTURE,  null,   true),
            'Match with offset'     => array('C:/foo/bar.baz.qux',  '{BAZ}i',             array('baz'),                                     null,                 4,      true),
            'No match with offset'  => array('C:/foo/bar.baz.qux',  '{BAZ}i',             array(),                                          null,                 5,      false),
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
        //                             path                   numLevels  parent
        return array(
            'Root'            => array('C:/',                 null,      'C:/..'),
            'Single atom'     => array('C:/foo',              null,      'C:/foo/..'),
            'Multiple atoms'  => array('C:/foo/bar',          null,      'C:/foo/bar/..'),
            'Up one level'    => array('C:/foo/bar/baz',      1,         'C:/foo/bar/baz/..'),
            'Up two levels'   => array('C:/foo/bar/baz',      2,         'C:/foo/bar/baz/../..'),
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
        //                               path             expectedResult
        return array(
            'Single atom'       => array('C:/foo/',       'C:/foo'),
            'Multiple atoms'    => array('C:/foo/bar/',   'C:/foo/bar'),
            'Whitespace atoms'  => array('C:/foo/bar /',  'C:/foo/bar '),
            'No trailing slash' => array('C:/foo',        'C:/foo'),
            'Root'              => array('C:/',           'C:/'),
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
        //                                   path               strippedExtension  strippedSuffix
        return array(
            'Root'                  => array('C:/',             'C:/',               'C:/'),
            'No extensions'         => array('C:/foo',          'C:/foo',            'C:/foo'),
            'Empty extension'       => array('C:/foo.',         'C:/foo',            'C:/foo'),
            'Whitespace extension'  => array('C:/foo . ',       'C:/foo ',           'C:/foo '),
            'Single extension'      => array('C:/foo.bar',      'C:/foo',            'C:/foo'),
            'Multiple extensions'   => array('C:/foo.bar.baz',  'C:/foo.bar',        'C:/foo'),
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
            'Single atom to root'              => array('C:/',         array('foo'),         'C:/foo'),
            'Multiple atoms to root'           => array('C:/',         array('foo', 'bar'),  'C:/foo/bar'),
            'Multiple atoms to multiple atoms' => array('C:/foo/bar',  array('baz', 'qux'),  'C:/foo/bar/baz/qux'),
            'Whitespace atoms'                 => array('C:/foo',      array(' '),           'C:/foo/ '),
            'Special atoms'                    => array('C:/foo',      array('.', '..'),     'C:/foo/./..'),
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
        $path = $this->factory->create('C:/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz/qux'. Path atoms must not contain separators."
        );
        $path->joinAtoms('bar', 'baz/qux');
    }

    public function testJoinAtomsFailureEmptyAtom()
    {
        $path = $this->factory->create('C:/foo');

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

    public function testJoinAtomSequenceWithNonArray()
    {
        $path = $this->factory->create('C:/foo');
        $result = $path->joinAtomSequence(new ArrayIterator(array('bar', 'baz')));

        $this->assertSame('C:/foo/bar/baz', $result->string());
    }

    public function testJoinAtomSequenceFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('C:/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz/qux'. Path atoms must not contain separators."
        );
        $path->joinAtomSequence(array('bar', 'baz/qux'));
    }

    public function testJoinAtomSequenceFailureEmptyAtom()
    {
        $path = $this->factory->create('C:/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\EmptyPathAtomException'
        );
        $path->joinAtomSequence(array('bar', ''));
    }

    public function joinData()
    {
        //                                              path           joinPath    expectedResult
        return array(
            'Single atom to root'              => array('C:/',         'foo',      'C:/foo'),
            'Multiple atoms to root'           => array('C:/',         'foo/bar',  'C:/foo/bar'),
            'Multiple atoms to multiple atoms' => array('C:/foo/bar',  'baz/qux',  'C:/foo/bar/baz/qux'),
            'Whitespace atoms'                 => array('C:/foo',      ' ',        'C:/foo/ '),
            'Special atoms'                    => array('C:/foo',      './..',     'C:/foo/./..'),

            'Relative with same drive'         => array('C:/foo',      'C:bar',    'C:/foo/bar'),
            'Anchored to root'                 => array('C:/',         '/foo',     'C:/foo'),
            'Anchored to multiple atoms'       => array('C:/foo/bar',  '/baz/qux', 'C:/baz/qux'),
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
        $path = $this->factory->create('C:/foo');
        $joinPath = $this->factory->create('C:/bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $path->join($joinPath);
    }

    public function testJoinFailureDriveMismatch()
    {
        $path = $this->factory->create('C:/foo');
        $joinPath = $this->factory->create('X:bar');

        $this->setExpectedException(__NAMESPACE__ . '\Exception\DriveMismatchException');
        $path->join($joinPath);
    }

    public function joinTrailingSlashData()
    {
        //                                     path           expectedResult
        return array(
            'Root atom'               => array('C:/',         'C:/'),
            'Single atom'             => array('C:/foo',      'C:/foo/'),
            'Whitespace atom'         => array('C:/foo ',     'C:/foo /'),
            'Multiple atoms'          => array('C:/foo/bar',  'C:/foo/bar/'),
            'Existing trailing slash' => array('C:/foo/',     'C:/foo/'),
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
        //                                                     path        extensions            expectedResult
        return array(
            'Add to root'                             => array('C:/',      array('foo'),         'C:/.foo'),
            'Empty extension'                         => array('C:/foo',   array(''),            'C:/foo.'),
            'Whitespace extension'                    => array('C:/foo',   array(' '),           'C:/foo. '),
            'Single extension'                        => array('C:/foo',   array('bar'),         'C:/foo.bar'),
            'Multiple extensions'                     => array('C:/foo',   array('bar', 'baz'),  'C:/foo.bar.baz'),
            'Empty extension with trailing slash'     => array('C:/foo/',  array(''),            'C:/foo.'),
            'Multiple extensions with trailing slash' => array('C:/foo/',  array('bar', 'baz'),  'C:/foo.bar.baz'),
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
        $path = $this->factory->create('C:/foo');

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
        $path = $this->factory->create('C:/foo');
        $result = $path->joinExtensionSequence(new ArrayIterator(array('bar', 'baz')));

        $this->assertSame('C:/foo.bar.baz', $result->string());
    }

    public function testJoinExtensionSequenceFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('C:/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo.bar/baz'. Path atoms must not contain separators."
        );
        $path->joinExtensionSequence(array('bar/baz'));
    }

    public function suffixNameData()
    {
        //                                                 path            suffix       expectedResult
        return array(
            'Root'                                => array('C:/',          'foo',       'C:/foo'),
            'Empty suffix'                        => array('C:/foo/bar',   '',          'C:/foo/bar'),
            'Empty suffix and trailing slash'     => array('C:/foo/bar/',  '',          'C:/foo/bar'),
            'Whitespace suffix'                   => array('C:/foo/bar',   ' ',         'C:/foo/bar '),
            'Normal suffix'                       => array('C:/foo/bar',   '-baz',      'C:/foo/bar-baz'),
            'Suffix with dots'                    => array('C:/foo/bar',   '.baz.qux',  'C:/foo/bar.baz.qux'),
            'Suffix with dots and trailing slash' => array('C:/foo/bar/',  '.baz.qux',  'C:/foo/bar.baz.qux'),
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
        $path = $this->factory->create('C:/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo/bar'. Path atoms must not contain separators."
        );
        $path->suffixName('/bar');
    }

    public function prefixNameData()
    {
        //                                                  path            prefix       expectedResult
        return array(
            'Root'                                 => array('C:/',          'foo',       'C:/foo'),
            'Empty prefix'                         => array('C:/foo/bar',   '',          'C:/foo/bar'),
            'Empty prefix and trailing slash'      => array('C:/foo/bar/',  '',          'C:/foo/bar'),
            'Whitespace prefix'                    => array('C:/foo/bar',   ' ',         'C:/foo/ bar'),
            'Normal prefix'                        => array('C:/foo/bar',   'baz-',      'C:/foo/baz-bar'),
            'Prefix with dots'                     => array('C:/foo/bar',   'baz.qux.',  'C:/foo/baz.qux.bar'),
            'Prefix with dots and trailing slash'  => array('C:/foo/bar/',  'baz.qux.',  'C:/foo/baz.qux.bar'),
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
        $path = $this->factory->create('C:/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'bar/foo'. Path atoms must not contain separators."
        );
        $path->prefixName('bar/');
    }

    public function replaceData()
    {
        //                                              path                   offset  replacement              length  expectedResult
        return array(
            'Replace single atom implicit'     => array('C:/foo/bar/baz/qux',  2,      array('doom'),           null,   'C:/foo/bar/doom'),
            'Replace multiple atoms implicit'  => array('C:/foo/bar/baz/qux',  1,      array('doom', 'splat'),  null,   'C:/foo/doom/splat'),
            'Replace single atom explicit'     => array('C:/foo/bar/baz/qux',  1,      array('doom'),           2,      'C:/foo/doom/qux'),
            'Replace multiple atoms explicit'  => array('C:/foo/bar/baz/qux',  1,      array('doom', 'splat'),  1,      'C:/foo/doom/splat/baz/qux'),
            'Replace atoms past end'           => array('C:/foo/bar/baz/qux',  111,    array('doom'),           222,    'C:/foo/bar/baz/qux/doom'),
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
        $path = $this->factory->create('C:/foo/bar/baz/qux');
        $result = $path->replace(1, new ArrayIterator(array('doom', 'splat')), 1);

        $this->assertSame('C:/foo/doom/splat/baz/qux', $result->string());
    }

    public function testReplaceFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('C:/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'bar/'. Path atoms must not contain separators."
        );
        $path->replace(1, array('bar/'));
    }

    public function replaceNameData()
    {
        //                                             path               name         expectedResult
        return array(
            'Root'                            => array('C:/',             'foo',       'C:/foo'),
            'Empty name'                      => array('C:/foo/bar',      '',          'C:/foo'),
            'Empty name with trailing slash'  => array('C:/foo/bar/',     '',          'C:/foo'),
            'Whitespace name'                 => array('C:/foo/bar',      ' ',         'C:/foo/ '),
            'Normal name'                     => array('C:/foo.bar.baz',  'qux',       'C:/qux'),
            'Normal name with extensions'     => array('C:/foo.bar.baz',  'qux.doom',  'C:/qux.doom'),
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
        $path = $this->factory->create('C:/foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'bar/'. Path atoms must not contain separators."
        );
        $path->replaceName('bar/');
    }

    public function replaceNameWithoutExtensionData()
    {
        //                                             path               name         expectedResult
        return array(
            'Root'                            => array('C:/',             'foo',       'C:/foo'),
            'Empty name'                      => array('C:/foo/bar',      '',          'C:/foo'),
            'Empty name with trailing slash'  => array('C:/foo/bar/',     '',          'C:/foo'),
            'Whitespace name'                 => array('C:/foo/bar',      ' ',         'C:/foo/ '),
            'Normal name'                     => array('C:/foo.bar.baz',  'qux',       'C:/qux.baz'),
            'Normal name with extensions'     => array('C:/foo.bar.baz',  'qux.doom',  'C:/qux.doom.baz'),
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
        $path = $this->factory->create('C:/foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'qux/.baz'. Path atoms must not contain separators."
        );
        $path->replaceNameWithoutExtension('qux/');
    }

    public function replaceNamePrefixData()
    {
        //                                             path               name         expectedResult
        return array(
            'Root'                            => array('C:/',             'foo',       'C:/foo'),
            'Empty name'                      => array('C:/foo/bar',      '',          'C:/foo'),
            'Empty name with trailing slash'  => array('C:/foo/bar/',     '',          'C:/foo'),
            'Whitespace name'                 => array('C:/foo/bar',      ' ',         'C:/foo/ '),
            'Normal name'                     => array('C:/foo.bar.baz',  'qux',       'C:/qux.bar.baz'),
            'Normal name with extensions'     => array('C:/foo.bar.baz',  'qux.doom',  'C:/qux.doom.bar.baz'),
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
        $path = $this->factory->create('C:/foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'qux/.bar.baz'. Path atoms must not contain separators."
        );
        $path->replaceNamePrefix('qux/');
    }

    public function replaceNameSuffixData()
    {
        //                                             path               name         expectedResult
        return array(
            'Root'                            => array('C:/',             'foo',       'C:/.foo'),
            'Empty name'                      => array('C:/foo/bar',      '',          'C:/foo/bar.'),
            'Empty name with trailing slash'  => array('C:/foo/bar/',     '',          'C:/foo/bar.'),
            'Whitespace name'                 => array('C:/foo/bar',      ' ',         'C:/foo/bar. '),
            'Normal name'                     => array('C:/foo.bar.baz',  'qux',       'C:/foo.qux'),
            'Normal name with extensions'     => array('C:/foo.bar.baz',  'qux.doom',  'C:/foo.qux.doom'),
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
        $path = $this->factory->create('C:/foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo.qux/'. Path atoms must not contain separators."
        );
        $path->replaceNameSuffix('qux/');
    }

    public function replaceExtensionData()
    {
        //                                             path               name         expectedResult
        return array(
            'Root'                            => array('C:/',             'foo',       'C:/.foo'),
            'Empty name'                      => array('C:/foo/bar',      '',          'C:/foo/bar.'),
            'Empty name with trailing slash'  => array('C:/foo/bar/',     '',          'C:/foo/bar.'),
            'Whitespace name'                 => array('C:/foo/bar',      ' ',         'C:/foo/bar. '),
            'Normal name'                     => array('C:/foo.bar.baz',  'qux',       'C:/foo.bar.qux'),
            'Normal name with extensions'     => array('C:/foo.bar.baz',  'qux.doom',  'C:/foo.bar.qux.doom'),
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
        $path = $this->factory->create('C:/foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo.bar.qux/'. Path atoms must not contain separators."
        );
        $path->replaceExtension('qux/');
    }

    public function replaceNameAtomsData()
    {
        //                                              path                   offset  replacement              length  expectedResult
        return array(
            'Replace single atom implicit'     => array('C:/foo.bar.baz.qux',  2,      array('doom'),           null,   'C:/foo.bar.doom'),
            'Replace multiple atoms implicit'  => array('C:/foo.bar.baz.qux',  1,      array('doom', 'splat'),  null,   'C:/foo.doom.splat'),
            'Replace single atom explicit'     => array('C:/foo.bar.baz.qux',  1,      array('doom'),           2,      'C:/foo.doom.qux'),
            'Replace multiple atoms explicit'  => array('C:/foo.bar.baz.qux',  1,      array('doom', 'splat'),  1,      'C:/foo.doom.splat.baz.qux'),
            'Replace atoms past end'           => array('C:/foo.bar.baz.qux',  111,    array('doom'),           222,    'C:/foo.bar.baz.qux.doom'),
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
        $path = $this->factory->create('C:/foo.bar.baz.qux');
        $result = $path->replaceNameAtoms(1, new ArrayIterator(array('doom', 'splat')), 1);

        $this->assertSame('C:/foo.doom.splat.baz.qux', $result->string());
    }

    public function testToAbsolute()
    {
        $path = $this->factory->create('C:/path/to/foo');

        $this->assertSame($path, $path->toAbsolute());
    }

    public function toRelativeData()
    {
        //                            path           expected
        return array(
            'Single atom'    => array('C:/foo',      'C:foo'),
            'Multiple atoms' => array('C:/foo/bar',  'C:foo/bar'),
            'Trailing slash' => array('C:/foo/bar/', 'C:foo/bar/'),
        );
    }

    /**
     * @dataProvider toRelativeData
     */
    public function testToRelative($pathString, $expected)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($expected, $path->toRelative()->string());
    }

    public function testNormalize()
    {
        $path = $this->factory->create('C:/foo/../bar');
        $normalizedPath = $this->factory->create('C:/bar');

        $this->assertEquals($normalizedPath, $path->normalize());
    }

    // // tests for AbsolutePathInterface implementation ==========================

    public function rootData()
    {
        //                                  path          isRoot
        return array(
            'Root'                 => array('C:/',        true),
            'Root non-normalized'  => array('C:/foo/..',  true),
            'Not root'             => array('C:/foo',     false),
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
        //                                       parent                child                        isParentOf  isAncestorOf
        return array(
            'Parent'                    => array('C:/foo',             'C:/foo/bar',                true,       true),
            'Root as parent'            => array('C:/',                'C:/foo',                    true,       true),
            'Resolve special atoms'     => array('C:/foo/bar/../baz',  'C:/foo/./baz/qux/../doom',  true,       true),
            'Not immediate parent'      => array('C:/foo',             'C:/foo/bar/baz',            false,      true),
            'Root not immediate parent' => array('C:/',                'C:/foo/bar',                false,      true),
            'Unrelated paths'           => array('C:/foo',             'C:/bar',                    false,      false),
            'Same paths'                => array('C:/foo/bar',         'C:/foor/bar',               false,      false),
            'Longer parent path'        => array('C:/foo/bar/baz',     'C:/foo',                    false,      false),

            'Mismatched drive'          => array('C:/foo',             'X:/foo/bar',                false,      false),
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

    public function testAncestryNonWindows()
    {
        $parent = $this->factory->create('C:/foo/bar');
        $child = $this->regularPathFactory->create('/foo/bar/baz');
        $grandchild = $this->regularPathFactory->create('/foo/bar/baz/qux');
        $nonChild = $this->regularPathFactory->create('/foo');

        $this->assertTrue($parent->isParentOf($child));
        $this->assertFalse($parent->isParentOf($grandchild));
        $this->assertFalse($parent->isParentOf($nonChild));
        $this->assertTrue($parent->isAncestorOf($child));
        $this->assertTrue($parent->isAncestorOf($grandchild));
        $this->assertFalse($parent->isAncestorOf($nonChild));
    }

    public function testIsParentOfFailureRelativeChild()
    {
        $parent = $this->factory->create('C:/foo');
        $child = $this->factory->create('foo/bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $parent->isParentOf($child);
    }

    public function testIsAncestorOfFailureRelativeChild()
    {
        $parent = $this->factory->create('C:/foo');
        $child = $this->factory->create('foo/bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $parent->isAncestorOf($child);
    }

    public function relativeToData()
    {
        //                                        parent                  child                 expectedResult
        return array(
            'Self'                       => array('C:/foo',               'C:/foo',               '.'),
            'Child'                      => array('C:/foo',               'C:/foo/bar',           'bar'),
            'Ancestor'                   => array('C:/foo',               'C:/foo/bar/baz',       'bar/baz'),
            'Sibling'                    => array('C:/foo',               'C:/bar',               '../bar'),
            'Parent\'s sibling'          => array('C:/foo/bar/baz',       'C:/foo/qux',           '../../qux'),
            'Parent\'s sibling\'s child' => array('C:/foo/bar/baz',       'C:/foo/qux/doom',      '../../qux/doom'),
            'Completely unrelated'       => array('C:/foo/bar/baz',       'C:/qux/doom',          '../../../qux/doom'),
            'Lengthly unrelated child'   => array('C:/foo/bar',           'C:/baz/qux/doom',      '../../baz/qux/doom'),
            'Common suffix'              => array('C:/foo/bar/baz/doom',  'C:/foo/bar/qux/doom',  '../../qux/doom'),

            'Mismatched drive'           => array('C:/foo',               'X:/foo/bar',           'X:foo/bar'),
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

    public function resolveAbsolutePathData()
    {
        //                                                     basePath              path               expectedResult
        return array(
            'Root against single atom'                => array('C:/',                'C:/foo',          'C:/foo'),
            'Single atom against single atom'         => array('C:/foo',             'C:/bar',          'C:/bar'),
            'Multiple atoms against single atom'      => array('C:/foo/bar',         'C:/baz',          'C:/baz'),
            'Multiple atoms against multiple atoms'   => array('C:/foo/../../bar',   'C:/baz/../qux',   'C:/baz/../qux'),

            'Mismatched drive'                        => array('C:/foo/../../bar',   'X:/baz/../qux',   'X:/baz/../qux'),
        );
    }

    /**
     * @dataProvider resolveAbsolutePathData
     */
    public function testResolveAbsolutePaths($basePathString, $pathString, $expectedResult)
    {
        $basePath = $this->factory->create($basePathString);
        $path = $this->factory->create($pathString);
        $resolved = $basePath->resolve($path);

        $this->assertSame($expectedResult, $resolved->string());
    }

    public function resolveRelativePathData()
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

            'Mismatched drives'                                                          => array('C:/foo/bar',   'X:foo/bar', 'X:/foo/bar'),
            'Anchored'                                                                   => array('C:/foo',       '/bar',      'C:/bar'),
        );
    }

    /**
     * @dataProvider resolveRelativePathData
     */
    public function testResolveRelativePaths($basePathString, $pathString, $expectedResult)
    {
        $basePath = $this->factory->create($basePathString);
        $path = $this->factory->create($pathString);
        $resolved = $basePath->resolve($path);

        $this->assertSame($expectedResult, $resolved->string());
    }

    // Static methods ==========================================================

    public function createData()
    {
        //                                                 path                       drive atoms                             hasTrailingSeparator
        return array(
            'Root'                                => array('C:/',                     'C',  array(),                          false),
            'Absolute'                            => array('C:/foo/bar',              'C',  array('foo', 'bar'),              false),
            'Absolute with trailing separator'    => array('C:/foo/bar/',             'C',  array('foo', 'bar'),              true),
            'Absolute with empty atoms'           => array('C:/foo//bar',             'C',  array('foo', 'bar'),              false),
            'Absolute with empty atoms at start'  => array('C://foo',                 'C',  array('foo'),                     false),
            'Absolute with empty atoms at end'    => array('C:/foo//',                'C',  array('foo'),                     true),
            'Absolute with whitespace atoms'      => array('C:/ foo bar / baz qux ',  'C',  array(' foo bar ', ' baz qux '),  false),
        );
    }

    /**
     * @dataProvider createData
     */
    public function testFromString($pathString, $drive, array $atoms, $hasTrailingSeparator)
    {
        $path = AbsoluteWindowsPath::fromString($pathString);

        $this->assertSame($drive, $path->drive());
        $this->assertSame($atoms, $path->atoms());
        $this->assertTrue($path instanceof AbsoluteWindowsPath);
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }

    public function testFromStringFailureRelative()
    {
        $this->setExpectedException('Eloquent\Pathogen\Exception\NonAbsolutePathException');
        AbsoluteWindowsPath::fromString('foo');
    }

    /**
     * @dataProvider createData
     */
    public function testFromDriveAndAtoms($pathString, $drive, array $atoms, $hasTrailingSeparator)
    {
        $path = AbsoluteWindowsPath::fromDriveAndAtoms($drive, $atoms, $hasTrailingSeparator);

        $this->assertSame($drive, $path->drive());
        $this->assertSame($atoms, $path->atoms());
        $this->assertTrue($path instanceof AbsoluteWindowsPath);
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
    }
}
