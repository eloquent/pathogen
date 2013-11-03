<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Unix;

use Eloquent\Pathogen\Factory\PathFactoryInterface;
use Eloquent\Pathogen\FileSystem\AbstractRelativeFileSystemPath;

/**
 * Represents a relative Unix path.
 */
class RelativeUnixPath extends AbstractRelativeFileSystemPath implements
    RelativeUnixPathInterface
{
    /**
     * Get the most appropriate path factory for this type of path.
     *
     * @return PathFactoryInterface The path factory.
     */
    protected static function factory()
    {
        return Factory\UnixPathFactory::instance();
    }
}
