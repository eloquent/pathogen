<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen;

use PHPUnit_Framework_TestCase;

/**
 * @covers \Eloquent\Pathogen\RelativePath
 * @covers \Eloquent\Pathogen\AbstractPath
 */
class RelativePathTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new Factory\PathFactory;
    }

    // tests for PathInterface implementation ==================================

    public function testEmptyPathConstructor()
    {
        $path = $this->factory->create('');

        $this->assertSame(array(), $path->atoms());
        $this->assertSame(false, $path->hasTrailingSeparator());
        $this->assertSame('.', $path->string());
        $this->assertSame('.', strval($path->string()));
    }

    public function testSelfPathConstructor()
    {
        $path = $this->factory->create('.');

        $this->assertSame(array('.'), $path->atoms());
        $this->assertSame(false, $path->hasTrailingSeparator());
        $this->assertSame('.', $path->string());
        $this->assertSame('.', strval($path));
    }

    public function pathData()
    {
        //                             path                     atoms                             expectedPathString      hasTrailingSeparator
        return array(
            'Empty'           => array('',                     array(),                           '.',                    false),
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
        $this->path = new RelativePath(array());

        $this->assertFalse($this->path->hasTrailingSeparator());
    }

    public function testConstructorFailureAtomContainingSeparator()
    {
        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo/bar'. Path atoms must not contain separators."
        );
        new RelativePath(array('foo/bar'));
    }

    public function testConstructorFailureEmptyAtom()
    {
        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\EmptyPathAtomException'
        );
        new RelativePath(array(''));
    }

    public function testEmptyWithTrailingSlashString()
    {
        $this->assertSame(
            './',
            $this->factory->createFromAtoms(array(), false, true)->string()
        );
    }

    public function namePartData()
    {
        //                                             path            name            nameWithoutExtension  namePrefix  nameSuffix  extension
        return array(
            'Self'                            => array('.',            '',             '',                   '',         null,       null),
            'Empty'                           => array('',             '',             '',                   '',         null,       null),
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

    public function parentData()
    {
        //                                   path                            parent
        return array(
            'Self'                           => array('.',                   '..'),
            'Empty'                          => array('',                    '..'),
            'Single atom'                    => array('foo',                 '.'),
            'Multiple atoms'                 => array('foo/bar/baz',         'foo/bar'),
            'Whitespace atoms'               => array('foo/ /bar',           'foo/ '),
            'Resolve special atoms'          => array('foo/./bar/../baz',    'foo'),
            'Resolve multiple special atoms' => array('foo/./bar/../../baz', '.'),
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
        //                               path          expectedResult
        return array(
            'Single atom'       => array('foo/',       'foo'),
            'Multiple atoms'    => array('foo/bar/',   'foo/bar'),
            'Whitespace atoms'  => array('foo/bar /',  'foo/bar '),
            'No trailing slash' => array('foo',        'foo'),
            'Self'              => array('.',          '.'),
            'Empty'             => array('',           '.'),
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
            'Empty'                 => array('',             '.',              '.'),
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
            'Single atom to empty'             => array('',          array('foo'),         'foo'),
            'Multiple atoms to self'           => array('.',         array('foo', 'bar'),  './foo/bar'),
            'Multiple atoms to empty'          => array('',          array('foo', 'bar'),  'foo/bar'),
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
            __NAMESPACE__ . '\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz/qux'. Path atoms must not contain separators."
        );
        $path->joinAtoms('bar', 'baz/qux');
    }

    public function testJoinAtomsFailureEmptyAtom()
    {
        $path = $this->factory->create('foo');

        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\EmptyPathAtomException'
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

    public function testJoinAtomSequenceFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('foo');

        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz/qux'. Path atoms must not contain separators."
        );
        $path->joinAtomSequence(array('bar', 'baz/qux'));
    }

    public function testJoinAtomSequenceFailureEmptyAtom()
    {
        $path = $this->factory->create('foo');

        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\EmptyPathAtomException'
        );
        $path->joinAtomSequence(array('bar', ''));
    }

    public function joinData()
    {
        //                                              path         joinPath    expectedResult
        return array(
            'Relative atom to self'            => array('.',        './foo',     '././foo'),
            'Relative atom to empty'           => array('',         './foo',     './foo'),
            'Single atom to empty'             => array('',         'foo',       'foo'),
            'Single atom to self'              => array('.',        'foo',       './foo'),
            'Multiple atoms to self'           => array('.',        './foo/bar', '././foo/bar'),
            'Multiple atoms to empty'          => array('',         './foo/bar', './foo/bar'),
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
            'Empty atom'              => array('',        './'),
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
            'Add to empty'                              => array('',       array('foo'),         '.foo'),
            'Add to self'                               => array('.',      array('foo'),         './.foo'),
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
            __NAMESPACE__ . '\Exception\PathAtomContainsSeparatorException',
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

    public function testJoinExtensionSequenceFailureAtomContainingSeparator()
    {
        $path = $this->factory->create('foo');

        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo.bar/baz'. Path atoms must not contain separators."
        );
        $path->joinExtensionSequence(array('bar/baz'));
    }

    public function suffixNameData()
    {
        //                                                 path         suffix       expectedResult
        return array(
            'Self'                                => array('.',         'foo',       'foo'),
            'Empty atom'                          => array('',          'foo',       'foo'),
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
            __NAMESPACE__ . '\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo/bar'. Path atoms must not contain separators."
        );
        $path->suffixName('/bar');
    }

    public function prefixNameData()
    {
        //                                                    path         prefix       expectedResult
        return array(
            'Self'                                   => array('.',         'foo',       'foo'),
            'Empty atom'                             => array('',          'foo',       'foo'),
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
            __NAMESPACE__ . '\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'bar/foo'. Path atoms must not contain separators."
        );
        $path->prefixName('bar/');
    }

    // tests for RelativePathInterface implementation ==========================

    public function isEmptyData()
    {
        return array(
            'Empty path'     => array('',         true),
            'Multiple atoms' => array('foo/bar',  false),
            'Self'           => array('.',        false),
            'Single atom'    => array('foo',      false),
        );
    }

    /**
     * @dataProvider isEmptyData
     */
    public function testIsEmpty($pathString, $isEmpty)
    {
        $path = $this->factory->create($pathString);

        $this->assertTrue($isEmpty === $path->isEmpty());
    }

    public function isSelfData()
    {
        return array(
            'Self'           => array('.',        true),
            'Empty path'     => array('',         false),
            'Single atom'    => array('foo',      false),
            'Multiple atoms' => array('foo/bar',  false),
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
}
