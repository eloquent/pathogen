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

/**
 * Represents an absolute Unix path.
 */
class AbsoluteUnixPath extends AbstractAbsoluteFileSystemPath implements
    AbsoluteUnixPathInterface
{
    /**
     * @param mixed<string> $atoms
     * @param boolean       $isAbsolute
     * @param boolean|null  $hasTrailingSeparator
     *
     * @return PathInterface
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
