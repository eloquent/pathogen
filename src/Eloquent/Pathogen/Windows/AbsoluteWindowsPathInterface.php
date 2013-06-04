<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows;

use Eloquent\Pathogen\AbsolutePathInterface;

/**
 * The interface implemented by absolute Windows paths.
 */
interface AbsoluteWindowsPathInterface extends
    AbsolutePathInterface,
    WindowsPathInterface
{
    /**
     * Get this path's drive specifier.
     *
     * @return string|null The drive specifier, or null if this path does not
     *     have a drive specifier.
     */
    public function drive();

    /**
     * Determine whether this path has a drive specifier.
     *
     * @return boolean True is this path has a drive specifier.
     */
    public function hasDrive();

    /**
     * Joins the supplied drive specifier to this path.
     *
     * @return string|null $drive The drive specifier to use, or null to remove
     *     the drive specifier.
     *
     * @return AbsoluteWindowsPathInterface A new path instance with the
     *     supplied drive specifier joined to this path.
     */
    public function joinDrive($drive);
}
