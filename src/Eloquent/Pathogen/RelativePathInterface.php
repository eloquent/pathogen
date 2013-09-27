<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen;

/**
 * The interface implemented by relative paths.
 */
interface RelativePathInterface extends PathInterface
{
    /**
     * Determine whether this path is the self path.
     *
     * The self path is a relative path with a single self atom (i.e. a dot
     * '.').
     *
     * @param Normalizer\PathNormalizerInterface|null $normalizer The normalizer to use when determining the result.
     *
     * @return boolean True if this path is the self path.
     */
    public function isSelf(
        Normalizer\PathNormalizerInterface $normalizer = null
    );
}
