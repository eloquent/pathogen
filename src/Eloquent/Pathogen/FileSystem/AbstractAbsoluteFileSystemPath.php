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

use Eloquent\Pathogen\AbsolutePath;
use Eloquent\Pathogen\Normalizer\PathNormalizerInterface;

/**
 * Represents an absolute file system path.
 */
abstract class AbstractAbsoluteFileSystemPath extends AbsolutePath implements
    AbsoluteFileSystemPathInterface
{
    // Implementation of PathInterface =========================================

    /**
     * Get the parent of this path a specified number of levels up.
     *
     * @param integer|null $numLevels The number of levels up.
     *     Defaults to 1.
     * @param PathNormalizerInterface|null $normalizer The normalizer to use
     *     when determining the parent.
     *
     * @return PathInterface The parent of this path $numLevels up.
     */
    public function parent(
        $numLevels = null,
        PathNormalizerInterface $normalizer = null
    ) {
        if (null === $normalizer) {
            $normalizer = $this->createDefaultNormalizer();
        }

        return parent::parent($numLevels, $normalizer);
    }

    // Implementation details ==================================================

    /**
     * @return PathNormalizerInterface
     */
    protected function createDefaultNormalizer()
    {
        return new Normalizer\FileSystemPathNormalizer;
    }
}
