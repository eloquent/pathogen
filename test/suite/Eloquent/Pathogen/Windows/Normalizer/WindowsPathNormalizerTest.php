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
        //                                                    pathString                   expectedResult
        return array(
            'Atom'                                   => array('C:/foo',                    'C:/foo'),
            'Atom, atom'                             => array('C:/foo/bar',                'C:/foo/bar'),
            'Atom, atom, atom, parent, parent, atom' => array('C:/foo/bar/baz/../../qux',  'C:/foo/qux'),
            'Atom, atom, parent'                     => array('C:/foo/bar/..',             'C:/foo'),
            'Atom, atom, slash'                      => array('C:/foo/bar/',               'C:/foo/bar'),
            'Atom, parent'                           => array('C:/foo/..',                 'C:/'),
            'Atom, parent, atom'                     => array('C:/foo/../bar',             'C:/bar'),
            'Atom, parent, atom, slash'              => array('C:/foo/../bar/',            'C:/bar'),
            'Atom, self, atom'                       => array('C:/foo/./bar',              'C:/foo/bar'),
            'Atom, self, atom, parent, atom'         => array('C:/foo/./bar/../baz',       'C:/foo/baz'),
            'Atom, self, atom, parent, parent, atom' => array('C:/foo/./bar/../../baz',    'C:/baz'),
            'Parent'                                 => array('C:/..',                     'C:/'),
            'Parent, atom'                           => array('C:/../foo',                 'C:/foo'),
            'Parent, atom, parent'                   => array('C:/../..',                  'C:/'),
            'Parent, parent'                         => array('C:/../..',                  'C:/'),
            'Parent, parent, atom'                   => array('C:/../../foo',              'C:/foo'),
            'Self'                                   => array('C:/.',                      'C:/'),
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
        //                                                                pathString                  expectedResult
        return array(
            'Atom'                                               => array('foo',                      'foo'),
            'Atom, atom'                                         => array('foo/bar',                  'foo/bar'),
            'Atom, atom, atom, parent, parent, atom'             => array('foo/bar/baz/../../qux',    'foo/qux'),
            'Atom, atom, parent'                                 => array('foo/bar/..',               'foo'),
            'Atom, atom, slash'                                  => array('foo/bar/',                 'foo/bar'),
            'Atom, parent'                                       => array('foo/..',                   '.'),
            'Atom, parent, atom'                                 => array('foo/../bar',               'bar'),
            'Atom, parent, atom, slash'                          => array('foo/../bar/',              'bar'),
            'Atom, self, atom'                                   => array('foo/./bar',                'foo/bar'),
            'Atom, self, atom, parent, atom'                     => array('foo/./bar/../baz',         'foo/baz'),
            'Parent'                                             => array('..',                       '..'),
            'Parent, atom'                                       => array('../foo',                   '../foo'),
            'Parent, atom, parent'                               => array('../foo/..',                '..'),
            'Parent, atom, parent, atom, self'                   => array('../foo/../bar/.',          '../bar'),
            'Parent, atom, parent, parent'                       => array('../foo/../..',             '../..'),
            'Parent, atom, parent, parent, parent'               => array('../foo/../../..',          '../../..'),
            'Parent, atom, parent, self, atom'                   => array('../foo/.././bar',          '../bar'),
            'Parent, atom, self'                                 => array('../foo/.',                 '../foo'),
            'Parent, atom, self, parent, atom'                   => array('../foo/./../bar',          '../bar'),
            'Parent, parent'                                     => array('../..',                    '../..'),
            'Parent, parent, atom, atom'                         => array('../../foo/bar',            '../../foo/bar'),
            'Parent, parent, atom, parent, atom'                 => array('../../foo/../bar',         '../../bar'),
            'Parent, parent, self, parent, self'                 => array('../.././../.',             '../../..'),
            'Parent, self, atom, parent, atom'                   => array('.././foo/../bar',          '../bar'),
            'Parent, self, parent'                               => array('.././..',                  '../..'),
            'Self'                                               => array('.',                        '.'),
            'Self, atom, parent'                                 => array('./foo/..',                 '.'),
            'Self, parent'                                       => array('./..',                     '..'),
            'Self, parent, atom'                                 => array('./../foo',                 '../foo'),
            'Self, self'                                         => array('./.',                      '.'),
            'Self, self, atom'                                   => array('././foo',                  'foo'),

            'Atom with drive'                                    => array('C:foo',                    'C:foo'),
            'Atom, atom with drive'                              => array('C:foo/bar',                'C:foo/bar'),
            'Atom, atom, atom, parent, parent, atom with drive'  => array('C:foo/bar/baz/../../qux',  'C:foo/qux'),
            'Atom, atom, parent with drive'                      => array('C:foo/bar/..',             'C:foo'),
            'Atom, atom, slash with drive'                       => array('C:foo/bar/',               'C:foo/bar'),
            'Atom, parent with drive'                            => array('C:foo/..',                 'C:.'),
            'Atom, parent, atom with drive'                      => array('C:foo/../bar',             'C:bar'),
            'Atom, parent, atom, slash with drive'               => array('C:foo/../bar/',            'C:bar'),
            'Atom, self, atom with drive'                        => array('C:foo/./bar',              'C:foo/bar'),
            'Atom, self, atom, parent, atom with drive'          => array('C:foo/./bar/../baz',       'C:foo/baz'),
            'Parent with drive'                                  => array('C:..',                     'C:..'),
            'Parent, atom with drive'                            => array('C:../foo',                 'C:../foo'),
            'Parent, atom, parent with drive'                    => array('C:../foo/..',              'C:..'),
            'Parent, atom, parent, atom, self with drive'        => array('C:../foo/../bar/.',        'C:../bar'),
            'Parent, atom, parent, parent with drive'            => array('C:../foo/../..',           'C:../..'),
            'Parent, atom, parent, parent, parent with drive'    => array('C:../foo/../../..',        'C:../../..'),
            'Parent, atom, parent, self, atom with drive'        => array('C:../foo/.././bar',        'C:../bar'),
            'Parent, atom, self with drive'                      => array('C:../foo/.',               'C:../foo'),
            'Parent, atom, self, parent, atom with drive'        => array('C:../foo/./../bar',        'C:../bar'),
            'Parent, parent with drive'                          => array('C:../..',                  'C:../..'),
            'Parent, parent, atom, atom with drive'              => array('C:../../foo/bar',          'C:../../foo/bar'),
            'Parent, parent, atom, parent, atom with drive'      => array('C:../../foo/../bar',       'C:../../bar'),
            'Parent, parent, self, parent, self with drive'      => array('C:../.././../.',           'C:../../..'),
            'Parent, self, atom, parent, atom with drive'        => array('C:.././foo/../bar',        'C:../bar'),
            'Parent, self, parent with drive'                    => array('C:.././..',                'C:../..'),
            'Self with drive'                                    => array('C:.',                      'C:.'),
            'Self, atom, parent with drive'                      => array('C:./foo/..',               'C:.'),
            'Self, parent with drive'                            => array('C:./..',                   'C:..'),
            'Self, parent, atom with drive'                      => array('C:./../foo',               'C:../foo'),
            'Self, self with drive'                              => array('C:./.',                    'C:.'),
            'Self, self, atom with drive'                        => array('C:././foo',                'C:foo'),

            'Atom anchored'                                      => array('/foo',                     '/foo'),
            'Atom, atom anchored'                                => array('/foo/bar',                 '/foo/bar'),
            'Atom, atom, atom, parent, parent, atom anchored'    => array('/foo/bar/baz/../../qux',   '/foo/qux'),
            'Atom, atom, parent anchored'                        => array('/foo/bar/..',              '/foo'),
            'Atom, atom, slash anchored'                         => array('/foo/bar/',                '/foo/bar'),
            'Atom, parent anchored'                              => array('/foo/..',                  '/'),
            'Atom, parent, atom anchored'                        => array('/foo/../bar',              '/bar'),
            'Atom, parent, atom, slash anchored'                 => array('/foo/../bar/',             '/bar'),
            'Atom, self, atom anchored'                          => array('/foo/./bar',               '/foo/bar'),
            'Atom, self, atom, parent, atom anchored'            => array('/foo/./bar/../baz',        '/foo/baz'),
            'Parent anchored'                                    => array('/..',                      '/'),
            'Parent, atom anchored'                              => array('/../foo',                  '/foo'),
            'Parent, atom, parent anchored'                      => array('/../foo/..',               '/'),
            'Parent, atom, parent, atom, self anchored'          => array('/../foo/../bar/.',         '/bar'),
            'Parent, atom, parent, parent anchored'              => array('/../foo/../..',            '/'),
            'Parent, atom, parent, parent, parent anchored'      => array('/../foo/../../..',         '/'),
            'Parent, atom, parent, self, atom anchored'          => array('/../foo/.././bar',         '/bar'),
            'Parent, atom, self anchored'                        => array('/../foo/.',                '/foo'),
            'Parent, atom, self, parent, atom anchored'          => array('/../foo/./../bar',         '/bar'),
            'Parent, parent anchored'                            => array('/../..',                   '/'),
            'Parent, parent, atom, atom anchored'                => array('/../../foo/bar',           '/foo/bar'),
            'Parent, parent, atom, parent, atom anchored'        => array('/../../foo/../bar',        '/bar'),
            'Parent, parent, self, parent, self anchored'        => array('/../.././../.',            '/'),
            'Parent, self, atom, parent, atom anchored'          => array('/.././foo/../bar',         '/bar'),
            'Parent, self, parent anchored'                      => array('/.././..',                 '/'),
            'Self anchored'                                      => array('/.',                       '/'),
            'Self, atom, parent anchored'                        => array('/./foo/..',                '/'),
            'Self, parent anchored'                              => array('/./..',                    '/'),
            'Self, parent, atom anchored'                        => array('/./../foo',                '/foo'),
            'Self, self anchored'                                => array('/./.',                     '/'),
            'Self, self, atom anchored'                          => array('/././foo',                 '/foo'),
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
