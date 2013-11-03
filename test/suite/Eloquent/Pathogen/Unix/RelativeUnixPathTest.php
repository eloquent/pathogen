<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Unix;

use ArrayIterator;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Pathogen\Unix\RelativeUnixPath
 * @covers \Eloquent\Pathogen\RelativePath
 * @covers \Eloquent\Pathogen\AbstractPath
 */
class RelativeUnixPathTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new Factory\UnixPathFactory;
    }

    // tests for PathInterface implementation ==================================

    public function pathData()
    {
        //                             path                     atoms                             expectedPathString      hasTrailingSeparator
        return array(
            'Self'            => array('.',                    array('.'),                        '.',                    false),
            'Single atom'     => array('foo',                  array('foo'),                      'foo',                  false),
            'Trailing slash'  => array('foo/',                 array('foo'),                      'foo/',                 true),
            'Multiple atoms'  => array('foo/bar',              array('foo', 'bar'),               'foo/bar',              false),
            'Parent atom'     => array('foo/../bar',           array('foo', '..', 'bar'),         'foo/../bar',           false),
            'Self atom'       => array('foo/./bar',            array('foo', '.', 'bar'),          'foo/./bar',            false),
            'Whitespace'      => array(' foo bar / baz qux ',  array(' foo bar ', ' baz qux '),   ' foo bar / baz qux ',  false),
        );
    }

    /**
     * @dataProvider pathData
     */
    public function testConstructor($pathString, array $atoms, $expectedPathString, $hasTrailingSeparator)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
        $this->assertSame($expectedPathString, $path->string());
        $this->assertSame($expectedPathString, strval($path->string()));
    }

    public function testConstructorDefaults()
    {
        $this->path = new RelativeUnixPath(array('.'));

        $this->assertFalse($this->path->hasTrailingSeparator());
    }

    public function testConstructorFailureAtomContainingSeparator()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo/bar'. Path atoms must not contain separators."
        );
        new RelativeUnixPath(array('foo/bar'));
    }

    public function testConstructorFailureEmptyAtom()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\EmptyPathAtomException'
        );
        new RelativeUnixPath(array(''));
    }

    public function testConstructorFailureEmptyPath()
    {
        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\EmptyPathException'
        );
        new RelativeUnixPath(array());
    }

    public function testAtomAt()
    {
        $path = $this->factory->create('foo/bar');

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
        //                                       path                needle       caseSensitive  expectedResult
        return array(
            'Empty'                     => array('.',                '',          null,          true),
            'Prefix'                    => array('foo/bar/baz.qux',  'FOO/BAR',   null,          true),
            'Middle'                    => array('foo/bar/baz.qux',  'BAR/BAZ',   null,          true),
            'Suffix'                    => array('foo/bar/baz.qux',  '/BAZ.QUX',  null,          true),
            'Not found'                 => array('foo/bar/baz.qux',  'DOOM',      null,          false),

            'Empty case sensitive'      => array('.',                '',          true,          true),
            'Prefix case sensitive'     => array('foo/bar/baz.qux',  'foo/bar',   true,          true),
            'Middle case sensitive'     => array('foo/bar/baz.qux',  'bar/baz',   true,          true),
            'Suffix case sensitive'     => array('foo/bar/baz.qux',  '/baz.qux',  true,          true),
            'Not found case sensitive'  => array('foo/bar/baz.qux',  'FOO',       true,          false),
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
        //                                       path                needle       caseSensitive  expectedResult
        return array(
            'Empty'                     => array('.',                '',          null,          true),
            'Prefix'                    => array('foo/bar/baz.qux',  'FOO/BAR',   null,          true),
            'Middle'                    => array('foo/bar/baz.qux',  'BAR/BAZ',   null,          false),
            'Suffix'                    => array('foo/bar/baz.qux',  '/BAZ.QUX',  null,          false),
            'Not found'                 => array('foo/bar/baz.qux',  'DOOM',      null,          false),

            'Empty case sensitive'      => array('.',                '',          true,          true),
            'Prefix case sensitive'     => array('foo/bar/baz.qux',  'foo/bar',   true,          true),
            'Middle case sensitive'     => array('foo/bar/baz.qux',  'bar/baz',   true,          false),
            'Suffix case sensitive'     => array('foo/bar/baz.qux',  '/baz.qux',  true,          false),
            'Not found case sensitive'  => array('foo/bar/baz.qux',  'FOO',       true,          false),
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
        //                                       path                needle       caseSensitive  expectedResult
        return array(
            'Empty'                     => array('.',                '',          null,          true),
            'Prefix'                    => array('foo/bar/baz.qux',  'FOO/BAR',   null,          false),
            'Middle'                    => array('foo/bar/baz.qux',  'BAR/BAZ',   null,          false),
            'Suffix'                    => array('foo/bar/baz.qux',  '/BAZ.QUX',  null,          true),
            'Not found'                 => array('foo/bar/baz.qux',  'DOOM',      null,          false),

            'Empty case sensitive'      => array('.',                '',          true,          true),
            'Prefix case sensitive'     => array('foo/bar/baz.qux',  'foo/bar',   true,          false),
            'Middle case sensitive'     => array('foo/bar/baz.qux',  'bar/baz',   true,          false),
            'Suffix case sensitive'     => array('foo/bar/baz.qux',  '/baz.qux',  true,          true),
            'Not found case sensitive'  => array('foo/bar/baz.qux',  'FOO',       true,          false),
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
        //                                         path                pattern        caseSensitive  flags          expectedResult
        return array(
            'Prefix'                      => array('foo/bar/baz.qux',  'FOO/BAR*',    null,          null,          true),
            'Middle'                      => array('foo/bar/baz.qux',  '*BAR/BAZ*',   null,          null,          true),
            'Suffix'                      => array('foo/bar/baz.qux',  '*/BAZ.QUX',   null,          null,          true),
            'Surrounding'                 => array('foo/bar/baz.qux',  'FOO*.QUX',    null,          null,          true),
            'Single character'            => array('foo/bar/baz.qux',  '*B?R*',       null,          null,          true),
            'Single character no match'   => array('foo/bar/baz.qux',  '*F?X*',       null,          null,          false),
            'Set'                         => array('foo/bar/baz.qux',  '*BA[RZ]*',    null,          null,          true),
            'Set no match'                => array('foo/bar/baz.qux',  '*BA[X]*',     null,          null,          false),
            'Negated set'                 => array('foo/bar/baz.qux',  '*BA[!RX]*',   null,          null,          true),
            'Negated set no match'        => array('foo/bar/baz.qux',  '*BA[!RZ]*',   null,          null,          false),
            'Range'                       => array('foo/bar/baz.qux',  '*BA[A-R]*',   null,          null,          true),
            'Range no match'              => array('foo/bar/baz.qux',  '*BA[S-Y]*',   null,          null,          false),
            'Negated range'               => array('foo/bar/baz.qux',  '*BA[!S-Y]*',  null,          null,          true),
            'Negated range no match'      => array('foo/bar/baz.qux',  '*BA[!R-Z]*',  null,          null,          false),
            'No partial match'            => array('foo/bar/baz.qux',  'BAR',         null,          null,          false),
            'Not found'                   => array('foo/bar/baz.qux',  'DOOM',        null,          null,          false),

            'Case sensitive'              => array('foo/bar/baz.qux',  '*bar/baz*',   true,          null,          true),
            'Case sensitive no match'     => array('foo/bar/baz.qux',  '*FOO*',       true,          null,          false),
            'Special flags'               => array('foo/bar/baz.qux',  'FOO/BAR/*',   false,         FNM_PATHNAME,  true),
            'Special flags no match'      => array('foo/bar/baz.qux',  '*FOO/BAR*',   false,         FNM_PATHNAME,  false),
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
        //                                   path                pattern              matches                                               flags                 offset  expectedResult
        return array(
            'Match'                 => array('foo/bar/baz.qux',  '{.*(FOO)/BAR.*}i',  array('foo/bar/baz.qux', 'foo'),                      null,                 null,   true),
            'No match'              => array('foo/bar/baz.qux',  '{.*DOOM.*}i',       array(),                                              null,                 null,   false),
            'Special flags'         => array('foo/bar/baz.qux',  '{.*FOO/(BAR).*}i',  array(array('foo/bar/baz.qux', 0), array('bar', 4)),  PREG_OFFSET_CAPTURE,  null,   true),
            'Match with offset'     => array('foo/bar/baz.qux',  '{BAR}i',            array('bar'),                                         null,                 4,      true),
            'No match with offset'  => array('foo/bar/baz.qux',  '{BAR}i',            array(),                                              null,                 5,      false),
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
        //                                       path                needle      caseSensitive  expectedResult
        return array(
            'Empty'                     => array('.',                '',         null,          true),
            'Prefix'                    => array('foo/bar.baz.qux',  'BAR.BAZ',  null,          true),
            'Middle'                    => array('foo/bar.baz.qux',  'BAZ',      null,          true),
            'Suffix'                    => array('foo/bar.baz.qux',  'BAZ.QUX',  null,          true),
            'Not found'                 => array('foo/bar.baz.qux',  'DOOM',     null,          false),
            'Match only in name'        => array('foo/bar.baz.qux',  'foo',      null,          false),

            'Empty case sensitive'      => array('.',                '',         true,          true),
            'Prefix case sensitive'     => array('foo/bar.baz.qux',  'bar.baz',  true,          true),
            'Middle case sensitive'     => array('foo/bar.baz.qux',  'baz',      true,          true),
            'Suffix case sensitive'     => array('foo/bar.baz.qux',  'baz.qux',  true,          true),
            'Not found case sensitive'  => array('foo/bar.baz.qux',  'BAR',      true,          false),
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
        //                                       path                needle      caseSensitive  expectedResult
        return array(
            'Empty'                     => array('.',                '',         null,          true),
            'Prefix'                    => array('foo/bar.baz.qux',  'BAR.BAZ',  null,          true),
            'Middle'                    => array('foo/bar.baz.qux',  'BAZ',      null,          false),
            'Suffix'                    => array('foo/bar.baz.qux',  'BAZ.QUX',  null,          false),
            'Not found'                 => array('foo/bar.baz.qux',  'DOOM',     null,          false),

            'Empty case sensitive'      => array('.',                '',         true,          true),
            'Prefix case sensitive'     => array('foo/bar.baz.qux',  'bar.baz',  true,          true),
            'Middle case sensitive'     => array('foo/bar.baz.qux',  'baz',      true,          false),
            'Suffix case sensitive'     => array('foo/bar.baz.qux',  'baz.qux',  true,          false),
            'Not found case sensitive'  => array('foo/bar.baz.qux',  'BAR',      true,          false),
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
        //                                         path                pattern        caseSensitive  flags        expectedResult
        return array(
            'Prefix'                      => array('foo/bar.baz.qux',  'BAR.BAZ*',    null,          null,        true),
            'Middle'                      => array('foo/bar.baz.qux',  '*BAZ*',       null,          null,        true),
            'Suffix'                      => array('foo/bar.baz.qux',  '*BAZ.QUX',    null,          null,        true),
            'Surrounding'                 => array('foo/bar.baz.qux',  'BAR.*.QUX',   null,          null,        true),
            'Single character'            => array('foo/bar.baz.qux',  '*B?R*',       null,          null,        true),
            'Single character no match'   => array('foo/bar.baz.qux',  '*B?X*',       null,          null,        false),
            'Set'                         => array('foo/bar.baz.qux',  '*BA[RZ]*',    null,          null,        true),
            'Set no match'                => array('foo/bar.baz.qux',  '*BA[X]*',     null,          null,        false),
            'Negated set'                 => array('foo/bar.baz.qux',  '*BA[!RX]*',   null,          null,        true),
            'Negated set no match'        => array('foo/bar.baz.qux',  '*BA[!RZ]*',   null,          null,        false),
            'Range'                       => array('foo/bar.baz.qux',  '*BA[A-R]*',   null,          null,        true),
            'Range no match'              => array('foo/bar.baz.qux',  '*BA[S-Y]*',   null,          null,        false),
            'Negated range'               => array('foo/bar.baz.qux',  '*BA[!S-Y]*',  null,          null,        true),
            'Negated range no match'      => array('foo/bar.baz.qux',  '*BA[!R-Z]*',  null,          null,        false),
            'No partial match'            => array('foo/bar.baz.qux',  'BAZ',         null,          null,        false),
            'Not found'                   => array('foo/bar.baz.qux',  'DOOM',        null,          null,        false),

            'Case sensitive'              => array('foo/bar.baz.qux',  '*baz*',       true,          null,        true),
            'Case sensitive no match'     => array('foo/bar.baz.qux',  '*BAZ*',       true,          null,        false),
            'Special flags'               => array('foo/.bar.baz',     '.bar*',       false,         FNM_PERIOD,  true),
            'Special flags no match'      => array('foo/.bar.baz',     '*bar*',       false,         FNM_PERIOD,  false),
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
        //                                   path                pattern               matches                                           flags                 offset  expectedResult
        return array(
            'Match'                 => array('foo/bar.baz.qux',  '{.*(BAR)\.BAZ.*}i',  array('bar.baz.qux', 'bar'),                      null,                 null,   true),
            'No match'              => array('foo/bar.baz.qux',  '{.*DOOM.*}i',        array(),                                          null,                 null,   false),
            'Special flags'         => array('foo/bar.baz.qux',  '{.*BAR\.(BAZ).*}i',  array(array('bar.baz.qux', 0), array('baz', 4)),  PREG_OFFSET_CAPTURE,  null,   true),
            'Match with offset'     => array('foo/bar.baz.qux',  '{BAZ}i',             array('baz'),                                     null,                 4,      true),
            'No match with offset'  => array('foo/bar.baz.qux',  '{BAZ}i',             array(),                                          null,                 5,      false),
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
        //                             path        numLevels  parent
        return array(
            'Self'            => array('.',        null,      '..'),
            'Single atom'     => array('foo',      null,      '.'),
            'Multiple atoms'  => array('foo/bar',  null,      'foo'),
            'Up one level'    => array('foo',      1,         '.'),
            'Up two levels'   => array('foo',      2,         '..'),
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
        //                               path          expectedResult
        return array(
            'Single atom'       => array('foo/',       'foo'),
            'Multiple atoms'    => array('foo/bar/',   'foo/bar'),
            'Whitespace atoms'  => array('foo/bar /',  'foo/bar '),
            'No trailing slash' => array('foo',        'foo'),
            'Self'              => array('.',          '.'),
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
        //                                   path            strippedExtension  strippedSuffix
        return array(
            'Self'                  => array('.',            '.',              '.'),
            'No extensions'         => array('foo',          'foo',            'foo'),
            'Empty extension'       => array('foo.',         'foo',            'foo'),
            'Whitespace extension'  => array('foo . ',       'foo ',           'foo '),
            'Single extension'      => array('foo.bar',      'foo',            'foo'),
            'Multiple extensions'   => array('foo.bar.baz',  'foo.bar',        'foo'),
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
        //                                              path         atoms                 expectedResult
        return array(
            'Single atom to self'              => array('.',         array('foo'),         './foo'),
            'Multiple atoms to self'           => array('.',         array('foo', 'bar'),  './foo/bar'),
            'Multiple atoms to multiple atoms' => array('foo/bar',   array('baz', 'qux'),  'foo/bar/baz/qux'),
            'Whitespace atoms'                 => array('foo',       array(' '),           'foo/ '),
            'Special atoms'                    => array('foo',       array('.', '..'),     'foo/./..'),
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
        $path = $this->factory->create('foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz/qux'. Path atoms must not contain separators."
        );
        $path->joinAtoms('bar', 'baz/qux');
    }

    public function testJoinAtomsFailureEmptyAtom()
    {
        $path = $this->factory->create('foo');

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
        $path = $this->factory->create('foo');
        $result = $path->joinAtomSequence(new ArrayIterator(array('bar', 'baz')));

        $this->assertSame('foo/bar/baz', $result->string());
    }

    public function testJoinAtomSequenceFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz/qux'. Path atoms must not contain separators."
        );
        $path->joinAtomSequence(array('bar', 'baz/qux'));
    }

    public function testJoinAtomSequenceFailureEmptyAtom()
    {
        $path = $this->factory->create('foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\EmptyPathAtomException'
        );
        $path->joinAtomSequence(array('bar', ''));
    }

    public function joinData()
    {
        //                                              path         joinPath    expectedResult
        return array(
            'Relative atom to self'            => array('.',        './foo',     '././foo'),
            'Single atom to self'              => array('.',        'foo',       './foo'),
            'Multiple atoms to self'           => array('.',        './foo/bar', '././foo/bar'),
            'Multiple atoms to multiple atoms' => array('foo/bar',  'baz/qux',   'foo/bar/baz/qux'),
            'Whitespace atoms'                 => array('foo',      ' ',         'foo/ '),
            'Special atoms'                    => array('foo',      './..',      'foo/./..'),
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
        $path = $this->factory->create('foo');
        $joinPath = $this->factory->create('/bar');

        $this->setExpectedException('PHPUnit_Framework_Error');
        $path->join($joinPath);
    }

    public function joinTrailingSlashData()
    {
        //                                     path       expectedResult
        return array(
            'Self atom'               => array('.',       './'),
            'Single atom'             => array('foo',     'foo/'),
            'Whitespace atom'         => array('foo ',    'foo /'),
            'Multiple atoms'          => array('foo/bar', 'foo/bar/'),
            'Existing trailing slash' => array('foo/',    'foo/'),
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
        //                                                       path      extensions            expectedResult
        return array(
            'Add to self'                               => array('.',      array('foo'),         '.foo'),
            'Empty extension'                           => array('foo',    array(''),            'foo.'),
            'Whitespace extension'                      => array('foo',    array(' '),           'foo. '),
            'Single extension'                          => array('foo',    array('bar'),         'foo.bar'),
            'Multiple extensions'                       => array('foo',    array('bar', 'baz'),  'foo.bar.baz'),
            'Empty extension with trailing slash'       => array('/foo/',  array(''),            '/foo.'),
            'Multiple extensions with trailing slash'   => array('/foo/',  array('bar', 'baz'),  '/foo.bar.baz'),
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
        $path = $this->factory->create('foo');

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
        $path = $this->factory->create('foo');
        $result = $path->joinExtensionSequence(new ArrayIterator(array('bar', 'baz')));

        $this->assertSame('foo.bar.baz', $result->string());
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
        //                                                 path         suffix       expectedResult
        return array(
            'Self'                                => array('.',         'foo',       'foo'),
            'Empty suffix'                        => array('foo/bar',   '',          'foo/bar'),
            'Empty suffix and trailing slash'     => array('foo/bar/',  '',          'foo/bar'),
            'Whitespace suffix'                   => array('foo/bar',   ' ',         'foo/bar '),
            'Normal suffix'                       => array('foo/bar',   '-baz',      'foo/bar-baz'),
            'Suffix with dots'                    => array('foo/bar',   '.baz.qux',  'foo/bar.baz.qux'),
            'Suffix with dots and trailing slash' => array('foo/bar',   '.baz.qux',  'foo/bar.baz.qux'),
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
        //                                                    path         prefix       expectedResult
        return array(
            'Self'                                   => array('.',         'foo',       'foo'),
            'Empty atom and trailing slash'          => array('./',        'foo',       'foo'),
            'Empty prefix'                           => array('foo/bar',   '',          'foo/bar'),
            'Whitespace prefix'                      => array('foo/bar',   ' ',         'foo/ bar'),
            'Normal prefix'                          => array('foo/bar',   'baz-',      'foo/baz-bar'),
            'Prefix with dots'                       => array('foo/bar',   'baz.qux.',  'foo/baz.qux.bar'),
            'Prefix with dots with trailing slash'   => array('foo/bar/',  'baz.qux.',  'foo/baz.qux.bar'),
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
        //                                              path                offset  replacement              length  expectedResult
        return array(
            'Replace single atom implicit'     => array('foo/bar/baz/qux',  2,      array('doom'),           null,   'foo/bar/doom'),
            'Replace multiple atoms implicit'  => array('foo/bar/baz/qux',  1,      array('doom', 'splat'),  null,   'foo/doom/splat'),
            'Replace single atom explicit'     => array('foo/bar/baz/qux',  1,      array('doom'),           2,      'foo/doom/qux'),
            'Replace multiple atoms explicit'  => array('foo/bar/baz/qux',  1,      array('doom', 'splat'),  1,      'foo/doom/splat/baz/qux'),
            'Replace atoms past end'           => array('foo/bar/baz/qux',  111,    array('doom'),           222,    'foo/bar/baz/qux/doom'),
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
        $path = $this->factory->create('foo/bar/baz/qux');
        $result = $path->replace(1, new ArrayIterator(array('doom', 'splat')), 1);

        $this->assertSame('foo/doom/splat/baz/qux', $result->string());
    }

    public function testReplaceFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'bar/'. Path atoms must not contain separators."
        );
        $path->replace(1, array('bar/'));
    }

    public function replaceNameData()
    {
        //                                             path            name         expectedResult
        return array(
            'Self'                            => array('.',            'foo',       'foo'),
            'Empty name'                      => array('foo/bar',      '',          'foo'),
            'Empty name with trailing slash'  => array('foo/bar/',     '',          'foo'),
            'Whitespace name'                 => array('foo/bar',      ' ',         'foo/ '),
            'Normal name'                     => array('foo.bar.baz',  'qux',       'qux'),
            'Normal name with extensions'     => array('foo.bar.baz',  'qux.doom',  'qux.doom'),
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
        $path = $this->factory->create('foo');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'bar/'. Path atoms must not contain separators."
        );
        $path->replaceName('bar/');
    }

    public function replaceNameWithoutExtensionData()
    {
        //                                             path            name         expectedResult
        return array(
            'Self'                            => array('.',            'foo',       'foo.'),
            'Empty name'                      => array('foo/bar',      '',          'foo'),
            'Empty name with trailing slash'  => array('foo/bar/',     '',          'foo'),
            'Whitespace name'                 => array('foo/bar',      ' ',         'foo/ '),
            'Normal name'                     => array('foo.bar.baz',  'qux',       'qux.baz'),
            'Normal name with extensions'     => array('foo.bar.baz',  'qux.doom',  'qux.doom.baz'),
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
        $path = $this->factory->create('foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'qux/.baz'. Path atoms must not contain separators."
        );
        $path->replaceNameWithoutExtension('qux/');
    }

    public function replaceNamePrefixData()
    {
        //                                             path            name         expectedResult
        return array(
            'Self'                            => array('.',            'foo',       'foo.'),
            'Empty name'                      => array('foo/bar',      '',          'foo'),
            'Empty name with trailing slash'  => array('foo/bar/',     '',          'foo'),
            'Whitespace name'                 => array('foo/bar',      ' ',         'foo/ '),
            'Normal name'                     => array('foo.bar.baz',  'qux',       'qux.bar.baz'),
            'Normal name with extensions'     => array('foo.bar.baz',  'qux.doom',  'qux.doom.bar.baz'),
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
        $path = $this->factory->create('foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'qux/.bar.baz'. Path atoms must not contain separators."
        );
        $path->replaceNamePrefix('qux/');
    }

    public function replaceNameSuffixData()
    {
        //                                             path            name         expectedResult
        return array(
            'Self'                            => array('.',            'foo',       '.foo'),
            'Empty name'                      => array('foo/bar',      '',          'foo/bar.'),
            'Empty name with trailing slash'  => array('foo/bar/',     '',          'foo/bar.'),
            'Whitespace name'                 => array('foo/bar',      ' ',         'foo/bar. '),
            'Normal name'                     => array('foo.bar.baz',  'qux',       'foo.qux'),
            'Normal name with extensions'     => array('foo.bar.baz',  'qux.doom',  'foo.qux.doom'),
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
        $path = $this->factory->create('foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo.qux/'. Path atoms must not contain separators."
        );
        $path->replaceNameSuffix('qux/');
    }

    public function replaceExtensionData()
    {
        //                                             path            name         expectedResult
        return array(
            'Self'                            => array('.',            'foo',       '.foo'),
            'Empty name'                      => array('foo/bar',      '',          'foo/bar.'),
            'Empty name with trailing slash'  => array('foo/bar/',     '',          'foo/bar.'),
            'Whitespace name'                 => array('foo/bar',      ' ',         'foo/bar. '),
            'Normal name'                     => array('foo.bar.baz',  'qux',       'foo.bar.qux'),
            'Normal name with extensions'     => array('foo.bar.baz',  'qux.doom',  'foo.bar.qux.doom'),
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
        $path = $this->factory->create('foo.bar.baz');

        $this->setExpectedException(
            'Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo.bar.qux/'. Path atoms must not contain separators."
        );
        $path->replaceExtension('qux/');
    }

    public function replaceNameAtomsData()
    {
        //                                              path                offset  replacement              length  expectedResult
        return array(
            'Replace single atom implicit'     => array('foo.bar.baz.qux',  2,      array('doom'),           null,   'foo.bar.doom'),
            'Replace multiple atoms implicit'  => array('foo.bar.baz.qux',  1,      array('doom', 'splat'),  null,   'foo.doom.splat'),
            'Replace single atom explicit'     => array('foo.bar.baz.qux',  1,      array('doom'),           2,      'foo.doom.qux'),
            'Replace multiple atoms explicit'  => array('foo.bar.baz.qux',  1,      array('doom', 'splat'),  1,      'foo.doom.splat.baz.qux'),
            'Replace atoms past end'           => array('foo.bar.baz.qux',  111,    array('doom'),           222,    'foo.bar.baz.qux.doom'),
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
        $path = $this->factory->create('foo.bar.baz.qux');
        $result = $path->replaceNameAtoms(1, new ArrayIterator(array('doom', 'splat')), 1);

        $this->assertSame('foo.doom.splat.baz.qux', $result->string());
    }

    public function toAbsoluteData()
    {
        //                            path        expected
        return array(
            'Single atom'    => array('foo',      '/foo'),
            'Multiple atoms' => array('foo/bar',  '/foo/bar'),
            'Trailing slash' => array('foo/bar/', '/foo/bar/'),
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
        //                                  path         isSelf
        return array(
            'Self'                 => array('.',         true),
            'Self non-normalized'  => array('./foo/..',  true),
            'Single atom'          => array('foo',       false),
            'Multiple atoms'       => array('foo/bar',   false),
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
        //                                                                                        basePath      path         expectedResult
        return array(
            'Root against single atom'                                                   => array('/',          'foo',       '/foo'),
            'Single atom against single atom'                                            => array('/foo',       'bar',       '/foo/bar'),
            'Multiple atoms against single atom'                                         => array('/foo/bar',   'baz',       '/foo/bar/baz'),
            'Multiple atoms with slash against single atoms'                             => array('/foo/bar/',  'baz',       '/foo/bar/baz'),
            'Multiple atoms against multiple atoms'                                      => array('/foo/bar',   'baz/qux',   '/foo/bar/baz/qux'),
            'Multiple atoms with slash against multiple atoms'                           => array('/foo/bar/',  'baz/qux',   '/foo/bar/baz/qux'),
            'Multiple atoms with slash against multiple atoms with slash'                => array('/foo/bar/',  'baz/qux/',  '/foo/bar/baz/qux'),
            'Root against parent atom'                                                   => array('/',          '..',        '/..'),
            'Single atom against parent atom'                                            => array('/foo',       '..',        '/foo/..'),
            'Single atom with slash against parent atom'                                 => array('/foo/',      '..',        '/foo/..'),
            'Single atom with slash against parent atom with slash'                      => array('/foo/',      '../',       '/foo/..'),
            'Multiple atoms against parent and single atom'                              => array('/foo/bar',   '../baz',    '/foo/bar/../baz'),
            'Multiple atoms with slash against parent atom and single atom'              => array('/foo/bar/',  '../baz',    '/foo/bar/../baz'),
            'Multiple atoms with slash against parent atom and single atom with slash'   => array('/foo/bar/',  '../baz/',   '/foo/bar/../baz'),
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
}
