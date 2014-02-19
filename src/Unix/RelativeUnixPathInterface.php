<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Unix;

use Eloquent\Pathogen\FileSystem\RelativeFileSystemPathInterface;

/**
 * The interface implemented by relative Unix paths.
 */
interface RelativeUnixPathInterface extends
    RelativeFileSystemPathInterface,
    UnixPathInterface
{
}
