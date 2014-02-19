<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Exception;

use Eloquent\Pathogen\PathInterface;

/**
 * Interface for exceptions that handle invalid path cases.
 */
interface InvalidPathExceptionInterface
{
    /**
     * Get the invalid path.
     *
     * @return PathInterface The invalid path.
     */
    public function path();

    /**
     * Get the reason message.
     *
     * @return string The reason message.
     */
    public function reason();
}
