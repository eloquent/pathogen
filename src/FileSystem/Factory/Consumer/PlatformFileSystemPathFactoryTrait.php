<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem\Factory\Consumer;

use Eloquent\Pathogen\Factory\Consumer\PathFactoryTrait;
use Eloquent\Pathogen\Factory\PathFactoryInterface;
use Eloquent\Pathogen\FileSystem\Factory\PlatformFileSystemPathFactory;

/**
 * A trait for classes that take a platform file system path factory as a
 * dependency.
 */
trait PlatformFileSystemPathFactoryTrait
{
    use PathFactoryTrait;

    /**
     * Create a new instance of the default path factory.
     *
     * @return PathFactoryInterface The new default path factory instance.
     */
    protected function createDefaultPathFactory()
    {
        return PlatformFileSystemPathFactory::instance();
    }
}
