<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows\Normalizer;

use Eloquent\Liberator\Liberator;
use Eloquent\Pathogen\Windows\Factory\WindowsPathFactory;
use PHPUnit_Framework_TestCase;

class WindowsPathNormalizerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new WindowsPathFactory;
        $this->normalizer = new WindowsPathNormalizer($this->factory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->factory, $this->normalizer->factory());
    }

    public function testConstructorDefaults()
    {
        $this->normalizer = new WindowsPathNormalizer;

        $this->assertEquals($this->factory, $this->normalizer->factory());
    }

    public function normalizeAbsolutePathData()
    {
        //                                                               pathString                   expectedResult
        return array(
            'Atom'                                              => array('/foo',                      '/foo'),
            'Atom, atom'                                        => array('/foo/bar',                  '/foo/bar'),
            'Atom, atom, atom, parent, parent, atom'            => array('/foo/bar/baz/../../qux',    '/foo/qux'),
            'Atom, atom, parent'                                => array('/foo/bar/..',               '/foo'),
            'Atom, atom, slash'                                 => array('/foo/bar/',                 '/foo/bar'),
            'Atom, parent'                                      => array('/foo/..',                   '/'),
            'Atom, parent, atom'                                => array('/foo/../bar',               '/bar'),
            'Atom, parent, atom, slash'                         => array('/foo/../bar/',              '/bar'),
            'Atom, self, atom'                                  => array('/foo/./bar',                '/foo/bar'),
            'Atom, self, atom, parent, atom'                    => array('/foo/./bar/../baz',         '/foo/baz'),
            'Atom, self, atom, parent, parent, atom'            => array('/foo/./bar/../../baz',      '/baz'),
            'Parent'                                            => array('/..',                       '/'),
            'Parent, atom'                                      => array('/../foo',                   '/foo'),
            'Parent, atom, parent'                              => array('/../..',                    '/'),
            'Parent, parent'                                    => array('/../..',                    '/'),
            'Parent, parent, atom'                              => array('/../../foo',                '/foo'),
            'Self'                                              => array('/.',                        '/'),

            'Atom with drive'                                   => array('C:/foo',                    'C:/foo'),
            'Atom, atom with drive'                             => array('C:/foo/bar',                'C:/foo/bar'),
            'Atom, atom, atom, parent, parent, atom with drive' => array('C:/foo/bar/baz/../../qux',  'C:/foo/qux'),
            'Atom, atom, parent with drive'                     => array('C:/foo/bar/..',             'C:/foo'),
            'Atom, atom, slash with drive'                      => array('C:/foo/bar/',               'C:/foo/bar'),
            'Atom, parent with drive'                           => array('C:/foo/..',                 'C:/'),
            'Atom, parent, atom with drive'                     => array('C:/foo/../bar',             'C:/bar'),
            'Atom, parent, atom, slash with drive'              => array('C:/foo/../bar/',            'C:/bar'),
            'Atom, self, atom with drive'                       => array('C:/foo/./bar',              'C:/foo/bar'),
            'Atom, self, atom, parent, atom with drive'         => array('C:/foo/./bar/../baz',       'C:/foo/baz'),
            'Atom, self, atom, parent, parent, atom with drive' => array('C:/foo/./bar/../../baz',    'C:/baz'),
            'Parent with drive'                                 => array('C:/..',                     'C:/'),
            'Parent, atom with drive'                           => array('C:/../foo',                 'C:/foo'),
            'Parent, atom, parent with drive'                   => array('C:/../..',                  'C:/'),
            'Parent, parent with drive'                         => array('C:/../..',                  'C:/'),
            'Parent, parent, atom with drive'                   => array('C:/../../foo',              'C:/foo'),
            'Self with drive'                                   => array('C:/.',                      'C:/'),
            'Drive uppercase'                                   => array('c:/foo/bar/',               'C:/foo/bar'),
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
        //                                                         pathString                expectedResult
        return array(
            'Atom'                                        => array('foo',                    'foo'),
            'Atom, atom'                                  => array('foo/bar',                'foo/bar'),
            'Atom, atom, atom, parent, parent, atom'      => array('foo/bar/baz/../../qux',  'foo/qux'),
            'Atom, atom, parent'                          => array('foo/bar/..',             'foo'),
            'Atom, atom, slash'                           => array('foo/bar/',               'foo/bar'),
            'Atom, parent'                                => array('foo/..',                 '.'),
            'Atom, parent, atom'                          => array('foo/../bar',             'bar'),
            'Atom, parent, atom, slash'                   => array('foo/../bar/',            'bar'),
            'Atom, self, atom'                            => array('foo/./bar',              'foo/bar'),
            'Atom, self, atom, parent, atom'              => array('foo/./bar/../baz',       'foo/baz'),
            'Parent'                                      => array('..',                     '..'),
            'Parent, atom'                                => array('../foo',                 '../foo'),
            'Parent, atom, parent'                        => array('../foo/..',              '..'),
            'Parent, atom, parent, atom, self'            => array('../foo/../bar/.',        '../bar'),
            'Parent, atom, parent, parent'                => array('../foo/../..',           '../..'),
            'Parent, atom, parent, parent, parent'        => array('../foo/../../..',        '../../..'),
            'Parent, atom, parent, self, atom'            => array('../foo/.././bar',        '../bar'),
            'Parent, atom, self'                          => array('../foo/.',               '../foo'),
            'Parent, atom, self, parent, atom'            => array('../foo/./../bar',        '../bar'),
            'Parent, parent'                              => array('../..',                  '../..'),
            'Parent, parent, atom, atom'                  => array('../../foo/bar',          '../../foo/bar'),
            'Parent, parent, atom, parent, atom'          => array('../../foo/../bar',       '../../bar'),
            'Parent, parent, self, parent, self'          => array('../.././../.',           '../../..'),
            'Parent, self, atom, parent, atom'            => array('.././foo/../bar',        '../bar'),
            'Parent, self, parent'                        => array('.././..',                '../..'),
            'Self'                                        => array('.',                      '.'),
            'Self, atom, parent'                          => array('./foo/..',               '.'),
            'Self, parent'                                => array('./..',                   '..'),
            'Self, parent, atom'                          => array('./../foo',               '../foo'),
            'Self, self'                                  => array('./.',                    '.'),
            'Self, self, atom'                            => array('././foo',                'foo'),
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
        $class = Liberator::liberateClass(__NAMESPACE__ . '\WindowsPathNormalizer');
        $class->instance = null;
        $actual = WindowsPathNormalizer::instance();

        $this->assertInstanceOf(__NAMESPACE__ . '\WindowsPathNormalizer', $actual);
        $this->assertSame($actual, WindowsPathNormalizer::instance());
    }
}
