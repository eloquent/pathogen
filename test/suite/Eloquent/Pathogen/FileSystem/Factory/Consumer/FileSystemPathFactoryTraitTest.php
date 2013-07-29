<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem\Factory\Consumer;

use Phake;
use PHPUnit_Framework_TestCase;

class FileSystemPathFactoryTraitTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if (!defined('T_TRAIT')) {
            $this->markTestSkipped('Requires trait support');
        }

        $this->consumer = $this->getObjectForTrait(
            __NAMESPACE__ . '\FileSystemPathFactoryTrait'
        );
    }

    public function testSetPathFactory()
    {
        $pathFactory = Phake::mock(
            'Eloquent\Pathogen\Factory\PathFactoryInterface'
        );
        $this->consumer->setPathFactory($pathFactory);

        $this->assertSame($pathFactory, $this->consumer->pathFactory());
    }

    public function testPathFactory()
    {
        $pathFactory = $this->consumer->pathFactory();

        $this->assertInstanceOf(
            'Eloquent\Pathogen\FileSystem\Factory\FileSystemPathFactory',
            $pathFactory
        );
        $this->assertSame($pathFactory, $this->consumer->pathFactory());
    }
}
