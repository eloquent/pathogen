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

use Eloquent\Pathogen\Factory\Consumer\PathFactoryTrait;
use Eloquent\Pathogen\FileSystem\Factory\FileSystemPathFactory;

/**
 * A trait for classes that take a file system path factory as a dependency.
 */
trait FileSystemPathFactoryTrait
{
    use PathFactoryTrait;

    /**
     * @return PathFactoryInterface
     */
    protected function createDefaultPathFactory()
    {
        return new FileSystemPathFactory;
    }
}
