<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem;

use Eloquent\Pathogen\Path;

/**
 * A static utility class for constructing file system paths.
 *
 * This class utilizes a path factory that produces file system paths whose type
 * correlates to the platform on which the code is running.
 *
 * Do not use this class in type hints; use FileSystemPathInterface instead.
 */
abstract class PlatformFileSystemPath extends Path
{
    /**
     * Create a path representing the current working directory.
     *
     * @return AbsoluteFileSystemPathInterface A new path instance representing the current working directory path.
     */
    public static function workingDirectoryPath()
    {
        return static::factory()->createWorkingDirectoryPath();
    }

    /**
     * Create a path representing the system temporary directory.
     *
     * @return AbsoluteFileSystemPathInterface A new path instance representing the system default temporary directory path.
     */
    public static function temporaryDirectoryPath()
    {
        return static::factory()->createTemporaryDirectoryPath();
    }

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
    public static function temporaryPath($prefix = null)
    {
        return static::factory()->createTemporaryPath($prefix);
    }

    /**
     * Get the most appropriate path factory for this type of path.
     *
     * @return Factory\FileSystemPathFactoryInterface The path factory.
     */
    protected static function factory()
    {
        return Factory\PlatformFileSystemPathFactory::instance();
    }
}
