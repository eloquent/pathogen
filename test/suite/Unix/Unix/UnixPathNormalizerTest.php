<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Unix\Normalizer;

use Eloquent\Liberator\Liberator;
use Eloquent\Pathogen\Unix\Factory\UnixPathFactory;
use PHPUnit_Framework_TestCase;

class UnixPathNormalizerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new UnixPathFactory;
        $this->normalizer = new UnixPathNormalizer($this->factory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->factory, $this->normalizer->factory());
    }

    public function testConstructorDefaults()
    {
        $this->normalizer = new UnixPathNormalizer;

        $this->assertEquals($this->factory, $this->normalizer->factory());
    }

    public function normalizeAbsolutePathData()
    {
        //                                                    pathString                 expectedResult
        return array(
            'Atom'                                   => array('/foo',                    '/foo'),
            'Atom, atom'                             => array('/foo/bar',                '/foo/bar'),
            'Atom, atom, atom, parent, parent, atom' => array('/foo/bar/baz/../../qux',  '/foo/qux'),
            'Atom, atom, parent'                     => array('/foo/bar/..',             '/foo'),
            'Atom, atom, slash'                      => array('/foo/bar/',               '/foo/bar'),
            'Atom, parent'                           => array('/foo/..',                 '/'),
            'Atom, parent, atom'                     => array('/foo/../bar',             '/bar'),
            'Atom, parent, atom, slash'              => array('/foo/../bar/',            '/bar'),
            'Atom, self, atom'                       => array('/foo/./bar',              '/foo/bar'),
            'Atom, self, atom, parent, atom'         => array('/foo/./bar/../baz',       '/foo/baz'),
            'Atom, self, atom, parent, parent, atom' => array('/foo/./bar/../../baz',    '/baz'),
            'Parent'                                 => array('/..',                     '/'),
            'Parent, atom'                           => array('/../foo',                 '/foo'),
            'Parent, atom, parent'                   => array('/../..',                  '/'),
            'Parent, parent'                         => array('/../..',                  '/'),
            'Parent, parent, atom'                   => array('/../../foo',              '/foo'),
            'Self'                                   => array('/.',                      '/'),
        );
    }

    /**
     * @dataProvider normalizeAbsolutePathData
     */
    public function testNormalizeAbsolutePaths($pathString, $expectedResult)
    {
        $path = $this->factory->create($pathString);
        $normalized = $this->normalizer->normalize($path);

        $this->assertSame($expectedResult, $normalized->string());
    }

    public function normalizeRelativePathData()
    {
        //                                                     pathString                expectedResult
        return array(
            'Atom'                                    => array('foo',                    'foo'),
            'Atom, atom'                              => array('foo/bar',                'foo/bar'),
            'Atom, atom, atom, parent, parent, atom'  => array('foo/bar/baz/../../qux',  'foo/qux'),
            'Atom, atom, parent'                      => array('foo/bar/..',             'foo'),
            'Atom, atom, slash'                       => array('foo/bar/',               'foo/bar'),
            'Atom, parent'                            => array('foo/..',                 '.'),
            'Atom, parent, atom'                      => array('foo/../bar',             'bar'),
            'Atom, parent, atom, slash'               => array('foo/../bar/',            'bar'),
            'Atom, self, atom'                        => array('foo/./bar',              'foo/bar'),
            'Atom, self, atom, parent, atom'          => array('foo/./bar/../baz',       'foo/baz'),
            'Parent'                                  => array('..',                     '..'),
            'Parent, atom'                            => array('../foo',                 '../foo'),
            'Parent, atom, parent'                    => array('../foo/..',              '..'),
            'Parent, atom, parent, atom, self'        => array('../foo/../bar/.',        '../bar'),
            'Parent, atom, parent, parent'            => array('../foo/../..',           '../..'),
            'Parent, atom, parent, parent, parent'    => array('../foo/../../..',        '../../..'),
            'Parent, atom, parent, self, atom'        => array('../foo/.././bar',        '../bar'),
            'Parent, atom, self'                      => array('../foo/.',               '../foo'),
            'Parent, atom, self, parent, atom'        => array('../foo/./../bar',        '../bar'),
            'Parent, parent'                          => array('../..',                  '../..'),
            'Parent, parent, atom, atom'              => array('../../foo/bar',          '../../foo/bar'),
            'Parent, parent, atom, parent, atom'      => array('../../foo/../bar',       '../../bar'),
            'Parent, parent, self, parent, self'      => array('../.././../.',           '../../..'),
            'Parent, self, atom, parent, atom'        => array('.././foo/../bar',        '../bar'),
            'Parent, self, parent'                    => array('.././..',                '../..'),
            'Self'                                    => array('.',                      '.'),
            'Self, atom, parent'                      => array('./foo/..',               '.'),
            'Self, parent'                            => array('./..',                   '..'),
            'Self, parent, atom'                      => array('./../foo',               '../foo'),
            'Self, self'                              => array('./.',                    '.'),
            'Self, self, atom'                        => array('././foo',                'foo'),
        );
    }

    /**
     * @dataProvider normalizeRelativePathData
     */
    public function testNormalizeRelativePaths($pathString, $expectedResult)
    {
        $path = $this->factory->create($pathString);
        $normalized = $this->normalizer->normalize($path);

        $this->assertSame($expectedResult, $normalized->string());
    }

    public function testInstance()
    {
        $class = Liberator::liberateClass(__NAMESPACE__ . '\UnixPathNormalizer');
        $class->instance = null;
        $actual = UnixPathNormalizer::instance();

        $this->assertInstanceOf(__NAMESPACE__ . '\UnixPathNormalizer', $actual);
        $this->assertSame($actual, UnixPathNormalizer::instance());
    }
}
