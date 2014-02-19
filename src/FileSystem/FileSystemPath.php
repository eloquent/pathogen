<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem;

use Eloquent\Pathogen\Path;

/**
 * A static utility class for constructing file system paths.
 *
 * This class utilizes a path factory that produces file system paths by
 * inspecting the supplied string, and determining the most suitable path type
 * to use.
 *
 * Do not use this class in type hints; use FileSystemPathInterface instead.
 */
abstract class FileSystemPath extends Path
{
    /**
     * Get the most appropriate path factory for this type of path.
     *
     * @return Factory\FileSystemPathFactoryInterface The path factory.
     */
    protected static function factory()
    {
        return Factory\FileSystemPathFactory::instance();
    }
}
