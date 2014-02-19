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
 * An attempt was made to construct a new path in an invalid state.
 */
final class InvalidPathStateException extends Exception
{
    /**
     * Construct a new invalid path state exception.
     *
     * @param string         $reason   The reason message.
     * @param Exception|null $previous The cause, if available.
     */
    public function __construct($reason, Exception $previous = null)
    {
        $this->reason = $reason;

        parent::__construct(
            sprintf('Invalid path state. %s', $reason),
            0,
            $previous
        );
    }

    /**
     * Get the reason message.
     *
     * @return string The reason message.
     */
    public function reason()
    {
        return $this->reason;
    }

    private $reason;
}
