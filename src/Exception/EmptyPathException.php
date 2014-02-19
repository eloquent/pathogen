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

use Exception;

/**
 * No path atoms were supplied when constructing a new relative path.
 */
final class EmptyPathException extends Exception
{
    /**
     * Construct a new empty path exception.
     *
     * @param Exception|null $previous The cause, if available.
     */
    public function __construct(Exception $previous = null)
    {
        parent::__construct(
            'Relative paths must have at least one atom.',
            0,
            $previous
        );
    }
}
