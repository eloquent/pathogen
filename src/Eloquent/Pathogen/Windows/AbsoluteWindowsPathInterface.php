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

interface AbsoluteWindowsPathInterface extends
    AbsolutePathInterface,
    WindowsPathInterface
{
    /**
     * @return string|null
     */
    public function drive();

    /**
     * @return boolean
     */
    public function hasDrive();

    /**
     * @return string|null $drive
     *
     * @return AbsoluteWindowsPathInterface
     */
    public function joinDrive($drive);
}
