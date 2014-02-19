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
 * An invalid character was encountered in a path atom.
 */
final class InvalidPathAtomCharacterException extends AbstractInvalidPathAtomException
{
    /**
     * Construct a new invalid path atom character exception.
     *
     * @param string         $atom      The invalid path atom.
     * @param string         $character The invalid character.
     * @param Exception|null $previous  The cause, if available.
     */
    public function __construct($atom, $character, Exception $previous = null)
    {
        $this->character = $character;

        parent::__construct($atom, $previous);
    }

    /**
     * Get the invalid character that caused the exception.
     *
     * @return string The invalid character.
     */
    public function character()
    {
        return $this->character;
    }

    /**
     * Get the reason message.
     *
     * @return string The reason message.
     */
    public function reason()
    {
        return sprintf(
            'Path atom contains invalid character %s.',
            var_export($this->character(), true)
        );
    }

    private $character;
}
