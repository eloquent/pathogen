<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem\Factory;

use Eloquent\Pathogen\Factory\PathFactoryInterface;
use Eloquent\Pathogen\FileSystem\AbsoluteFileSystemPathInterface;

/**
 * The interface implemented by path factories that deal with file system paths.
 */
interface FileSystemPathFactoryInterface extends PathFactoryInterface
{
    /**
     * Create a path representing the current working directory.
     *
     * @return AbsoluteFileSystemPathInterface A new path instance representing the current working directory path.
     */
    public function createWorkingDirectoryPath();

    /**
     * Create a path representing the system temporary directory.
     *
     * @return AbsoluteFileSystemPathInterface A new path instance representing the system default temporary directory path.
     */
    public function createTemporaryDirectoryPath();

    /**
     * Create a path representing a suitable for use as the location for a new
     * temporary file or directory.
     *
     * This path is not guaranteed to be unused, but collisions are fairly
     * unlikely.
     *
     * @param string|null $prefix A string to use as a prefix for the path name.
     *
     * @return AbsoluteFileSystemPathInterface A new path instance representing the new temporary path.
     */
    public function createTemporaryPath($prefix = null);
}
