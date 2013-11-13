<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows\Resolver;

use Eloquent\Liberator\Liberator;
use Eloquent\Pathogen\Factory\PathFactory;
use Eloquent\Pathogen\Windows\Factory\WindowsPathFactory;
use PHPUnit_Framework_TestCase;

class WindowsPathResolverTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->resolver = new WindowsPathResolver;
        $this->factory = new WindowsPathFactory;
        $this->regularFactory = new PathFactory;
    }

    public function resolveAbsolutePathData()
    {
        //                                                                          basePath            path             expectedResult
        return array(
            'Root against single atom with same drive'                     => array('C:/',              'C:/foo',        'C:/foo'),
            'Single atom against single atom with same drive'              => array('C:/foo',           'C:/bar',        'C:/bar'),
            'Multiple atoms against single atom with same drive'           => array('C:/foo/bar',       'C:/baz',        'C:/baz'),
            'Multiple atoms against multiple atoms with same drive'        => array('C:/foo/../../bar', 'C:/baz/../qux', 'C:/baz/../qux'),

            'Root against single atom with different drive'                => array('C:/',              'X:/foo',        'X:/foo'),
            'Single atom against single atom with different drive'         => array('C:/foo',           'X:/bar',        'X:/bar'),
            'Multiple atoms against single atom with different drive'      => array('C:/foo/bar',       'X:/baz',        'X:/baz'),
            'Multiple atoms against multiple atoms with different drive'   => array('C:/foo/../../bar', 'X:/baz/../qux', 'X:/baz/../qux'),
        );
    }

    /**
     * @dataProvider resolveAbsolutePathData
     */
    public function testResolveAbsolutePaths($basePathString, $pathString, $expectedResult)
    {
        $basePath = $this->factory->create($basePathString);
        $path = $this->factory->create($pathString);
        $resolved = $this->resolver->resolve($basePath, $path);

        $this->assertSame($expectedResult, $resolved->string());
    }

    public function resolveRelativePathData()
    {
        //                                                                                                           basePath        path          expectedResult
        return array(
            'Root against single atom'                                                                      => array('C:/',          'foo',        'C:/foo'),
            'Single atom against single atom'                                                               => array('C:/foo',       'bar',        'C:/foo/bar'),
            'Multiple atoms against single atom'                                                            => array('C:/foo/bar',   'baz',        'C:/foo/bar/baz'),
            'Multiple atoms with slash against single atoms'                                                => array('C:/foo/bar/',  'baz',        'C:/foo/bar/baz'),
            'Multiple atoms against multiple atoms'                                                         => array('C:/foo/bar',   'baz/qux',    'C:/foo/bar/baz/qux'),
            'Multiple atoms with slash against multiple atoms'                                              => array('C:/foo/bar/',  'baz/qux',    'C:/foo/bar/baz/qux'),
            'Multiple atoms with slash against multiple atoms with slash'                                   => array('C:/foo/bar/',  'baz/qux/',   'C:/foo/bar/baz/qux'),
            'Root against parent atom'                                                                      => array('C:/',          '..',         'C:/..'),
            'Single atom against parent atom'                                                               => array('C:/foo',       '..',         'C:/foo/..'),
            'Single atom with slash against parent atom'                                                    => array('C:/foo/',      '..',         'C:/foo/..'),
            'Single atom with slash against parent atom with slash'                                         => array('C:/foo/',      '../',        'C:/foo/..'),
            'Multiple atoms against parent and single atom'                                                 => array('C:/foo/bar',   '../baz',     'C:/foo/bar/../baz'),
            'Multiple atoms with slash against parent atom and single atom'                                 => array('C:/foo/bar/',  '../baz',     'C:/foo/bar/../baz'),
            'Multiple atoms with slash against parent atom and single atom with slash'                      => array('C:/foo/bar/',  '../baz/',    'C:/foo/bar/../baz'),

            'Root against single atom with same drive'                                                      => array('C:/',          'C:foo',      'C:/foo'),
            'Single atom against single atom with same drive'                                               => array('C:/foo',       'C:bar',      'C:/foo/bar'),
            'Multiple atoms against single atom with same drive'                                            => array('C:/foo/bar',   'C:baz',      'C:/foo/bar/baz'),
            'Multiple atoms with slash against single atoms with same drive'                                => array('C:/foo/bar/',  'C:baz',      'C:/foo/bar/baz'),
            'Multiple atoms against multiple atoms with same drive'                                         => array('C:/foo/bar',   'C:baz/qux',  'C:/foo/bar/baz/qux'),
            'Multiple atoms with slash against multiple atoms with same drive'                              => array('C:/foo/bar/',  'C:baz/qux',  'C:/foo/bar/baz/qux'),
            'Multiple atoms with slash against multiple atoms with slash with same drive'                   => array('C:/foo/bar/',  'C:baz/qux/', 'C:/foo/bar/baz/qux'),
            'Root against parent atom with same drive'                                                      => array('C:/',          'C:..',       'C:/..'),
            'Single atom against parent atom with same drive'                                               => array('C:/foo',       'C:..',       'C:/foo/..'),
            'Single atom with slash against parent atom with same drive'                                    => array('C:/foo/',      'C:..',       'C:/foo/..'),
            'Single atom with slash against parent atom with slash with same drive'                         => array('C:/foo/',      'C:../',      'C:/foo/..'),
            'Multiple atoms against parent and single atom with same drive'                                 => array('C:/foo/bar',   'C:../baz',   'C:/foo/bar/../baz'),
            'Multiple atoms with slash against parent atom and single atom with same drive'                 => array('C:/foo/bar/',  'C:../baz',   'C:/foo/bar/../baz'),
            'Multiple atoms with slash against parent atom and single atom with slash with same drive'      => array('C:/foo/bar/',  'C:../baz/',  'C:/foo/bar/../baz'),

            'Root against single atom with different drive'                                                 => array('C:/',          'X:foo',      'X:/foo'),
            'Single atom against single atom with different drive'                                          => array('C:/foo',       'X:bar',      'X:/bar'),
            'Multiple atoms against single atom with different drive'                                       => array('C:/foo/bar',   'X:baz',      'X:/baz'),
            'Multiple atoms with slash against single atoms with different drive'                           => array('C:/foo/bar/',  'X:baz',      'X:/baz'),
            'Multiple atoms against multiple atoms with different drive'                                    => array('C:/foo/bar',   'X:baz/qux',  'X:/baz/qux'),
            'Multiple atoms with slash against multiple atoms with different drive'                         => array('C:/foo/bar/',  'X:baz/qux',  'X:/baz/qux'),
            'Multiple atoms with slash against multiple atoms with slash with different drive'              => array('C:/foo/bar/',  'X:baz/qux/', 'X:/baz/qux'),
            'Root against parent atom with different drive'                                                 => array('C:/',          'X:..',       'X:/..'),
            'Single atom against parent atom with different drive'                                          => array('C:/foo',       'X:..',       'X:/..'),
            'Single atom with slash against parent atom with different drive'                               => array('C:/foo/',      'X:..',       'X:/..'),
            'Single atom with slash against parent atom with slash with different drive'                    => array('C:/foo/',      'X:../',      'X:/..'),
            'Multiple atoms against parent and single atom with different drive'                            => array('C:/foo/bar',   'X:../baz',   'X:/../baz'),
            'Multiple atoms with slash against parent atom and single atom with different drive'            => array('C:/foo/bar/',  'X:../baz',   'X:/../baz'),
            'Multiple atoms with slash against parent atom and single atom with slash with different drive' => array('C:/foo/bar/',  'X:../baz/',  'X:/../baz'),

            'Anchored root against single atom'                                                             => array('C:/',          '/foo',       'C:/foo'),
            'Anchored single atom against single atom'                                                      => array('C:/foo',       '/bar',       'C:/bar'),
            'Anchored multiple atoms against single atom'                                                   => array('C:/foo/bar',   '/baz',       'C:/baz'),
            'Anchored multiple atoms with slash against single atoms'                                       => array('C:/foo/bar/',  '/baz',       'C:/baz'),
            'Anchored multiple atoms against multiple atoms'                                                => array('C:/foo/bar',   '/baz/qux',   'C:/baz/qux'),
            'Anchored multiple atoms with slash against multiple atoms'                                     => array('C:/foo/bar/',  '/baz/qux',   'C:/baz/qux'),
            'Anchored multiple atoms with slash against multiple atoms with slash'                          => array('C:/foo/bar/',  '/baz/qux/',  'C:/baz/qux'),
            'Anchored root against parent atom'                                                             => array('C:/',          '/..',        'C:/..'),
            'Anchored single atom against parent atom'                                                      => array('C:/foo',       '/..',        'C:/..'),
            'Anchored single atom with slash against parent atom'                                           => array('C:/foo/',      '/..',        'C:/..'),
            'Anchored single atom with slash against parent atom with slash'                                => array('C:/foo/',      '/../',       'C:/..'),
            'Anchored multiple atoms against parent and single atom'                                        => array('C:/foo/bar',   '/../baz',    'C:/../baz'),
            'Anchored multiple atoms with slash against parent atom and single atom'                        => array('C:/foo/bar/',  '/../baz',    'C:/../baz'),
            'Anchored multiple atoms with slash against parent atom and single atom with slash'             => array('C:/foo/bar/',  '/../baz/',   'C:/../baz'),
        );
    }

    /**
     * @dataProvider resolveRelativePathData
     */
    public function testResolveRelativePaths($basePathString, $pathString, $expectedResult)
    {
        $basePath = $this->factory->create($basePathString);
        $path = $this->factory->create($pathString);
        $resolved = $this->resolver->resolve($basePath, $path);

        $this->assertSame($expectedResult, $resolved->string());
    }

    public function testInstance()
    {
        $class = Liberator::liberateClass(__NAMESPACE__ . '\WindowsPathResolver');
        $class->instance = null;
        $actual = WindowsPathResolver::instance();

        $this->assertInstanceOf(__NAMESPACE__ . '\WindowsPathResolver', $actual);
        $this->assertSame($actual, WindowsPathResolver::instance());
    }
}
