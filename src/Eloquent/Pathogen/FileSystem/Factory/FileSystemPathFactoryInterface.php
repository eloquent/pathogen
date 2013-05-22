<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem\Factory;

use Eloquent\Pathogen\AbsolutePathInterface;
use Eloquent\Pathogen\Factory\PathFactoryInterface;

interface FileSystemPathFactoryInterface extends PathFactoryInterface
{
    /**
     * Returns a new path instance representing the current working directory
     * path.
     *
     * @return AbsolutePathInterface
     */
    public function createWorkingDirectoryPath();

    /**
     * Returns a new path instance representing the system default temporary
     * directory path.
     *
     * @return AbsolutePathInterface
     */
    public function createTemporaryDirectoryPath();

    /**
     * Returns a new path instance representing a path suitable for use as the
     * location for a new temporary file or directory.
     *
     * @param string|null $prefix A string to use as a prefix for the path name.
     *
     * @return AbsolutePathInterface
     */
    public function createTemporaryPath($prefix = null);
}
