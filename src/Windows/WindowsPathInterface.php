<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows;

use Eloquent\Pathogen\FileSystem\FileSystemPathInterface;

/**
 * The interface implemented by all Windows paths.
 */
interface WindowsPathInterface extends FileSystemPathInterface
{
    /**
     * Get this path's drive specifier.
     *
     * Absolute Windows paths always have a drive specifier, and will never
     * return null for this method.
     *
     * @return string|null The drive specifier, or null if this path does not have a drive specifier.
     */
    public function drive();

    /**
     * Determine whether this path has a drive specifier.
     *
     * Absolute Windows paths always have a drive specifier, and will always
     * return true for this method.
     *
     * @return boolean True is this path has a drive specifier.
     */
    public function hasDrive();

    /**
     * Returns true if this path's drive specifier matches the supplied drive
     * specifier.
     *
     * This method is not case sensitive.
     *
     * @param string|null $drive The driver specifier to compare to.
     *
     * @return boolean True if the drive specifiers match.
     */
    public function matchesDrive($drive);

    /**
     * Returns true if this path's drive specifier matches the supplied drive
     * specifier, or if either drive specifier is null.
     *
     * This method is not case sensitive.
     *
     * @param string|null $drive The driver specifier to compare to.
     *
     * @return boolean True if the drive specifiers match, or either drive specifier is null.
     */
    public function matchesDriveOrNull($drive);

    /**
     * Joins the supplied drive specifier to this path.
     *
     * @return string|null $drive The drive specifier to use, or null to remove the drive specifier.
     *
     * @return WindowsPathInterface A new path instance with the supplied drive specifier joined to this path.
     */
    public function joinDrive($drive);
}
