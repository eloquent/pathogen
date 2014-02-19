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
 * Abstract base class for exceptions implementing
 * InvalidPathAtomExceptionInterface.
 */
abstract class AbstractInvalidPathAtomException extends Exception
    implements InvalidPathAtomExceptionInterface
{
    /**
     * Construct a new invalid path atom exception.
     *
     * @param string         $atom     The invalid path atom.
     * @param Exception|null $previous The cause, if available.
     */
    public function __construct($atom, Exception $previous = null)
    {
        $this->atom = $atom;

        parent::__construct(
            sprintf(
                "Invalid path atom %s. %s",
                var_export($atom, true),
                $this->reason()
            ),
            0,
            $previous
        );
    }

    /**
     * Get the invalid path atom.
     *
     * @return string The invalid path atom.
     */
    public function atom()
    {
        return $this->atom;
    }

    /**
     * Get the reason message.
     *
     * @return string The reason message.
     */
    abstract public function reason();

    private $atom;
}
