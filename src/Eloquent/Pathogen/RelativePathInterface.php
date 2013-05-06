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

interface RelativePathInterface extends PathInterface
{
    /**
     * Returns true if this path is the empty path.
     *
     * The empty path is a relative path with no atoms.
     *
     * @return boolean
     */
    public function isEmpty();

    /**
     * Returns true if this path is the self path.
     *
     * The self path is a relative path with a single self atom (i.e. a dot
     * '.').
     *
     * @return boolean
     */
    public function isSelf();
}
