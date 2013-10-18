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

use Eloquent\Pathogen\FileSystem\AbstractAbsoluteFileSystemPath;
use Eloquent\Pathogen\PathInterface;

/**
 * Represents an absolute Unix path.
 */
class AbsoluteUnixPath extends AbstractAbsoluteFileSystemPath implements
    AbsoluteUnixPathInterface
{
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
            return new AbsoluteUnixPath($atoms, $hasTrailingSeparator);
        }

        return new RelativeUnixPath($atoms, $hasTrailingSeparator);
    }
}
