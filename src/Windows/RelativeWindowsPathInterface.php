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

use Eloquent\Pathogen\FileSystem\RelativeFileSystemPathInterface;

/**
 * The interface implemented by relative Windows paths.
 */
interface RelativeWindowsPathInterface extends
    RelativeFileSystemPathInterface,
    WindowsPathInterface
{
    /**
     * Returns true if this path is 'anchored' to the drive root.
     *
     * This is a special case to represent almost-absolute Windows paths where
     * the drive is not present, but the path is still specified as starting
     * from the root of the drive.
     *
     * For example, the Windows path `\path\to\foo` represents the path
     * `C:\path\to\foo` when resolved against the `C:` drive.
     *
     * @return boolean True if this path is anchored.
     */
    public function isAnchored();
}
