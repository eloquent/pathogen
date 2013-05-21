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

final class InvalidPathAtomCharacterException extends AbstractInvalidPathAtomException
{
    /**
     * @param string         $atom
     * @param string         $character
     * @param Exception|null $previous
     */
    public function __construct($atom, $character, Exception $previous = null)
    {
        $this->character = $character;

        parent::__construct($atom, $previous);
    }

    /**
     * @return string
     */
    public function character()
    {
        return $this->character;
    }

    /**
     * @return string
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
