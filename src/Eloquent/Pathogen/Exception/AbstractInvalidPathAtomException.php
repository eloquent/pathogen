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

abstract class AbstractInvalidPathAtomException extends Exception
    implements InvalidPathAtomExceptionInterface
{
    /**
     * @param string         $atom
     * @param Exception|null $previous
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
     * Returns the invalid path atom.
     *
     * @return string
     */
    public function atom()
    {
        return $this->atom;
    }

    /**
     * @return string
     */
    abstract public function reason();

    private $atom;
}
