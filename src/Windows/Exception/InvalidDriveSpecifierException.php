<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows\Exception;

use Exception;

/**
 * The provided drive specifier is invalid.
 */
final class InvalidDriveSpecifierException extends Exception
{
    /**
     * Constructs a new invalid drive specifier exception.
     *
     * @param string         $drive    The invalid drive specifier.
     * @param Exception|null $previous The cause, if available.
     */
    public function __construct($drive, Exception $previous = null)
    {
        $this->drive = $drive;

        parent::__construct(
            sprintf('Invalid drive specifier %s.', var_export($drive, true)),
            0,
            $previous
        );
    }

    /**
     * Get the invalid drive specifier.
     *
     * @return string The invalid drive specifier.
     */
    public function drive()
    {
        return $this->drive;
    }

    private $drive;
}
