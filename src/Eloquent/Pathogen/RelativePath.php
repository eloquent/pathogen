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

class RelativePath extends AbstractPath implements RelativePathInterface
{
    /**
     * Returns a string representation of this path.
     *
     * @return string
     */
    public function string()
    {
        return $this->hasAtoms() ? parent::string() : '.';
    }

    /**
     * Returns the parent of this path.
     *
     * @return PathInterface
     */
    public function parent()
    {
        $atoms = $this->atoms();
        $atoms[] = '..';

        return $this->factory()->createFromAtoms($atoms, false, false);
    }

    /**
     * Returns true if this path is the empty path.
     *
     * The empty path is a relative path with no atoms.
     *
     * @return boolean
     */
    public function isEmpty()
    {

    }

    /**
     * Returns true if this path is the self path.
     *
     * The self path is a relative path with a single self atom (i.e. a dot '.').
     *
     * @return boolean
     */
    public function isSelf()
    {

    }
}
