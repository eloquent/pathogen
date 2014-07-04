<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Unix;

use Eloquent\Pathogen\Factory\PathFactoryInterface;
use Eloquent\Pathogen\FileSystem\RelativeFileSystemPathInterface;
use Eloquent\Pathogen\Normalizer\PathNormalizerInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\RelativePath;

/**
 * Represents a relative Unix path.
 */
class RelativeUnixPath extends RelativePath implements
    RelativeFileSystemPathInterface,
    RelativeUnixPathInterface
{
    // Implementation details ==================================================

    /**
     * Creates a new path instance of the most appropriate type.
     *
     * This method is called internally every time a new path instance is
     * created as part of another method call. It can be overridden in child
     * classes to change which classes are used when creating new path
     * instances.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param boolean       $isAbsolute           True if the new path should be absolute.
     * @param boolean|null  $hasTrailingSeparator True if the new path should have a trailing separator.
     *
     * @return PathInterface The newly created path instance.
     */
    protected function createPath(
        $atoms,
        $isAbsolute,
        $hasTrailingSeparator = null
    ) {
        if ($isAbsolute) {
            return AbsoluteUnixPath::constructUnsafe(
                $atoms,
                $hasTrailingSeparator
            );
        }

        return RelativeUnixPath::constructUnsafe($atoms, $hasTrailingSeparator);
    }

    /**
     * Get the most appropriate path factory for this type of path.
     *
     * @return PathFactoryInterface The path factory.
     */
    protected static function factory()
    {
        return Factory\UnixPathFactory::instance();
    }

    /**
     * Get the most appropriate path normalizer for this type of path.
     *
     * @return PathNormalizerInterface The path normalizer.
     */
    protected static function normalizer()
    {
        return Normalizer\UnixPathNormalizer::instance();
    }
}
