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

use Eloquent\Pathogen\Factory\PathFactoryInterface;
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
     * Get the most appropriate path factory for this type of path.
     *
     * @return PathFactoryInterface The path factory.
     */
    protected static function factory()
    {
        return Factory\PlatformFileSystemPathFactory::instance();
    }
}
