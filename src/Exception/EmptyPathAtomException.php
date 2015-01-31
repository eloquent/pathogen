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
 * An empty path atom was supplied.
 */
final class EmptyPathAtomException extends AbstractInvalidPathAtomException
{
    /**
     * Construct a new empty path atom exception.
     *
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct(Exception $cause = null)
    {
        parent::__construct('', $cause);
    }

    /**
     * Get the reason message.
     *
     * @return string The reason message.
     */
    public function reason()
    {
        return 'Path atoms must not be empty strings.';
    }
}
