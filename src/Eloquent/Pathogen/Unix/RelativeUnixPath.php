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
    // Implementation of PathInterface =========================================

    /**
     * Get the parent of this path a specified number of levels up.
     *
     * @param integer|null $numLevels The number of levels up. Defaults to 1.
     *
     * @return PathInterface The parent of this path $numLevels up.
     */
    public function parent($numLevels = null)
    {
        return parent::parent($numLevels)->normalize();
    }

    // Implementation details ==================================================

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
