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
 * The requested path could not be determined.
 */
final class UnknownPathException extends Exception
{
    /**
     * Construct a new unknown path exception.
     *
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct(Exception $cause = null)
    {
        parent::__construct(
            'The requested path could not be determined.',
            0,
            $cause
        );
    }
}
