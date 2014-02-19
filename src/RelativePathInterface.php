<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
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
     * @return boolean True if this path is the self path.
     */
    public function isSelf();

    /**
     * Resolve this path against the supplied path.
     *
     * @param AbsolutePathInterface $basePath The path to resolve against.
     *
     * @return AbsolutePathInterface The resolved path.
     */
    public function resolveAgainst(AbsolutePathInterface $basePath);
}
