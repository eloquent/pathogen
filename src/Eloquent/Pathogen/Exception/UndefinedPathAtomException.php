<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Exception;

use Exception;

/**
 * An undefined path atom was requested.
 */
final class UndefinedPathAtomException extends Exception
{
    /**
     * Construct a new undefined path atom exception.
     *
     * @param integer        $index    The requested atom index.
     * @param Exception|null $previous The cause, if available.
     */
    public function __construct($index, Exception $previous = null)
    {
        $this->index = $index;

        parent::__construct(
            sprintf(
                'No path atom defined for index %s.',
                var_export($index, true)
            ),
            0,
            $previous
        );
    }

    /**
     * Get the requested atom index.
     *
     * @return string The requested index.
     */
    public function index()
    {
        return $this->index;
    }

    private $index;
}
