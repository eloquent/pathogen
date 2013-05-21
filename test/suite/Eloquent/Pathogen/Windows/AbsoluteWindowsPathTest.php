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

    public function parentData()
    {
        //                                        path           parent
        return array(
            'Root'                       => array('/',           '/..'),
            'Single atom'                => array('/foo',        '/foo/..'),
            'Multiple atoms'             => array('/foo/bar',    '/foo/bar/..'),

            'Root with drive'            => array('C:/',         'C:/..'),
            'Single atom with drive'     => array('C:/foo',      'C:/foo/..'),
            'Multiple atoms with drive'  => array('C:/foo/bar',  'C:/foo/bar/..'),
        );
    }

    /**
     * @dataProvider parentData
     */
    public function testParent($pathString, $parentPathString)
    {
        $path = $this->factory->create($pathString);
        $parentPath = $path->parent();

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

    // tests for AbsolutePathInterface implementation ==========================

    public function testIsRoot()
    {
        $this->assertTrue($this->factory->create('/')->isRoot());
        $this->assertFalse($this->factory->create('/foo')->isRoot());
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
        );
    }

    /**
     * @dataProvider relativeToData
     */
    public function testRelativeTo($parentString, $childString, $expectedResultString)
    {
        $parent = $this->factory->create($parentString);
        $child = $this->factory->create($childString);
        $result = $parent->relativeTo($child);

        $this->assertSame($expectedResultString, $result->string());
    }

    public function testRelativeToWithRegularPath()
    {
        $parent = $this->factory->create('/foo');
        $child = $this->regularPathFactory->create('/foo/bar');
        $result = $parent->relativeTo($child);

        $this->assertSame('bar', $result->string());
    }

    public function testRelativeToFailureDriveMismatch()
    {
        $parent = $this->factory->create('C:/foo');
        $child = $this->factory->create('D:/foo/bar');

        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\DriveMismatchException',
            "Drive specifiers 'C' and 'D' do not match."
        );
        $parent->relativeTo($child);
    }

    public function testRelativeToFailureDriveMismatchRegularPath()
    {
        $parent = $this->factory->create('C:/foo');
        $child = $this->regularPathFactory->create('/foo/bar');

        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\DriveMismatchException',
            "Drive specifiers 'C' and NULL do not match."
        );
        $parent->relativeTo($child);
    }
}
