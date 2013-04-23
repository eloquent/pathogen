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
 * @covers AbsolutePath
 * @covers AbstractPath
 */
class AbsolutePathTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new Factory\PathFactory;
    }

    // tests for PathInterface implementation ==================================

    public function pathData()
    {
        //                               path            atoms                       hasTrailingSeparator
        return array(
            'Root'              => array('/',            array(),                    false),
            'Single atom'       => array('/foo',         array('foo'),               false),
            'Trailing slash'    => array('/foo/',        array('foo'),               true),
            'Multiple atoms'    => array('/foo/bar',     array('foo', 'bar'),        false),
            'Parent atom'       => array('/foo/../bar',  array('foo', '..', 'bar'),  false),
            'Self atom'         => array('/foo/./bar',   array('foo', '.', 'bar'),   false),
        );
    }

    /**
     * @dataProvider pathData
     */
    public function testConstructor($pathString, array $atoms, $hasTrailingSeparator)
    {
        $path = $this->factory->create($pathString);

        $this->assertSame($atoms, $path->atoms());
        $this->assertSame($hasTrailingSeparator, $path->hasTrailingSeparator());
        $this->assertSame($pathString, $path->string());
        $this->assertSame($pathString, strval($path->string()));
    }

    public function testConstructorFailureAtomContainingSeparator()
    {
        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'foo/bar'. Path atoms must not contain separators."
        );
        new AbsolutePath(array('foo/bar'));
    }

    public function testConstructorFailureEmptyAtom()
    {
        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\EmptyPathAtomException'
        );
        new AbsolutePath(array(''));
    }

    public function parentData()
    {
        //                                   path                  parent
        return array(
            'Single atom'           => array('/foo',               '/'),
            'Multiple atoms'        => array('/foo/bar/baz',       '/foo/bar'),
            'Resolve special atoms' => array('/foo/./bar/../baz',  '/foo'),
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

    public function testParentFailureRootPath()
    {
        $path = $this->factory->create('/');

        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\RootParentException'
        );
        $path->parent();
    }

    public function testStripTrailingSlash()
    {
        $path = $this->factory->create('/foo/bar/');

        $this->assertSame('/foo/bar', $path->stripTrailingSlash()->string());
    }

    public function stripTrailingSlashFailureData()
    {
        //                               path
        return array(
            'No trailing slash' => array('/foo'),
            'Root'              => array('/'),
        );
    }

    /**
     * @dataProvider stripTrailingSlashFailureData
     */
    public function testStripTrailingSlashFailure($pathString)
    {
        $path = $this->factory->create($path);

        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\NoTrailingSlashException'
        );
        $path->stripTrailingSlash();
    }

    public function joinAtomsData()
    {
        //                                              path         atoms                 expectedResult
        array(
            'Single atom to root'              => array('/',         array('foo'),         '/foo'),
            'Multiple atoms to root'           => array('/',         array('foo', 'bar'),  '/foo/bar'),
            'Multiple atoms to multiple atoms' => array('/foo/bar',  array('baz', 'qux'),  '/foo/bar/baz/qux'),
            'Special atoms'                    => array('/foo',      array('.', '..'),     '/foo/./..'),
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
            __NAMESPACE__ . '\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz/qux'. Path atoms must not contain separators."
        );
        $path->joinAtoms('bar', 'baz/qux');
    }

    public function testJoinAtomsFailureEmptyAtom()
    {
        $path = $this->factory->create('/foo');

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
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\PathAtomContainsSeparatorException',
            "Invalid path atom 'baz/qux'. Path atoms must not contain separators."
        );
        $path->joinAtomSequence(array('bar', 'baz/qux'));
    }

    public function testJoinAtomSequenceFailureEmptyAtom()
    {
        $path = $this->factory->create('/foo');

        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\EmptyPathAtomException'
        );
        $path->joinAtomSequence(array('bar', ''));
    }

    public function joinData()
    {
        //                                              path         joinPath    expectedResult
        array(
            'Single atom to root'              => array('/',         'foo',      '/foo'),
            'Multiple atoms to root'           => array('/',         'foo/bar',  '/foo/bar'),
            'Multiple atoms to multiple atoms' => array('/foo/bar',  'baz/qux',  '/foo/bar/baz/qux'),
            'Special atoms'                    => array('/foo',      './..',     '/foo/./..'),
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

        $this->setExpectedException(
            'ErrorException',
            'Argument 1 passed to Eloquent\Pathogen\AbsolutePath::join() ' .
                'must be an instance of Eloquent\Pathogen\RelativePathInterface, ' .
                'instance of Eloquent\Pathogen\AbsolutePath given'
        );
        $path->join($joinPath);
    }

    public function testJoinTrailingSlash()
    {
        $path = $this->create('/foo');

        $this->assertSame('/foo/', $path->joinTrailingSlash()->string());
    }

    public function testJoinTrailingSlashFailureRoot()
    {
        $path = $this->create('/');

        $this->setExpectedException(
            __NAMESPACE__ . '\Exception\RootTrailingSlashException'
        );
        $path->joinTrailingSlash();
    }

    // tests for AbsolutePathInterface implementation ==========================

    public function testIsRoot()
    {
        $this->assertTrue($this->factory->create('/')->isRoot());
        $this->assertFalse($this->factory->create('/foo')->isRoot());
    }

    public function ancestryData()
    {
        //                                       parent              child                      isParentOf  isAncestorOf
        return array(
            'Parent'                    => array('/foo',             '/foo/bar',                true,       true),
            'Root as parent'            => array('/',                '/foo',                    true,       true),
            'Resolve special atoms'     => array('/foo/bar/../baz',  '/foo/./baz/qux/../doom',  true,       true),
            'Not immediate parent'      => array('/foo',             '/foo/bar/baz',            false,      true),
            'Root not immediate parent' => array('/',                '/foo/bar',                false,      true),
            'Unrelated paths'           => array('/foo',             '/bar',                    false,      false),
        );
    }

    /**
     * @dataProvider ancestorData
     */
    public function testAncestry($parentString, $childString, $isParentOf, $isAncestorOf)
    {
        $parent = $this->factory->create($parentString);
        $child = $this->factory->create($childString);

        $this->assertSame($isParentOf, $parent->isParentOf($child));
        $this->assertSame($isAncestorOf, $parent->isAncestorOf($child));
    }

    public function testIsParentOfFailureRelativeChild()
    {
        $parent = $this->factory->create('/foo');
        $child = $this->factory->create('foo/bar');

        $this->setExpectedException(
            'ErrorException',
            'Argument 1 passed to Eloquent\Pathogen\AbsolutePath::isParentOf() ' .
                'must be an instance of Eloquent\Pathogen\AbsolutePathInterface, ' .
                'instance of Eloquent\Pathogen\RelativePath given'
        );
        $path->isParentOf($joinPath);
    }

    public function testIsAncestorOfFailureRelativeChild()
    {
        $parent = $this->factory->create('/foo');
        $child = $this->factory->create('foo/bar');

        $this->setExpectedException(
            'ErrorException',
            'Argument 1 passed to Eloquent\Pathogen\AbsolutePath::isAncestorOf() ' .
                'must be an instance of Eloquent\Pathogen\AbsolutePathInterface, ' .
                'instance of Eloquent\Pathogen\RelativePath given'
        );
        $path->isAncestorOf($joinPath);
    }

    public function relativeToData()
    {
        return array(
            'Self'                       => array('/foo',          '/foo',           '.'),
            'Child'                      => array('/foo',          '/foo/bar',       'bar'),
            'Ancestor'                   => array('/foo',          '/foo/bar/baz',   'bar/baz'),
            'Sibling'                    => array('/foo',          '/bar',           '../bar'),
            'Parent\'s sibling'          => array('/foo/bar/baz',  '/foo/qux',       '../../qux'),
            'Parent\'s sibling\'s child' => array('/foo/bar/baz',  '/foo/qux/doom',  '../../qux/doom'),
            'Completely unrelated'       => array('/foo/bar/baz',  '/qux/doom',      '../../../qux/doom'),
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
}
