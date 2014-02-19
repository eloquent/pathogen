<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem;

use Eloquent\Pathogen\AbsolutePathInterface;

/**
 * The interface implemented by absolute file system paths.
 */
interface AbsoluteFileSystemPathInterface extends
    AbsolutePathInterface,
    FileSystemPathInterface
{
}
