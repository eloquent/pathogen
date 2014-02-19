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
 * The drive specifiers of the two paths do not match.
 */
final class DriveMismatchException extends Exception
{
    /**
     * Constructs a new drive specifier mismatch exception.
     *
     * @param string         $leftDrive  The left-hand drive specifier.
     * @param string         $rightDrive The right-hand drive specifier.
     * @param Exception|null $previous   The cause, if available.
     */
    public function __construct(
        $leftDrive,
        $rightDrive,
        Exception $previous = null
    ) {
        $this->leftDrive = $leftDrive;
        $this->rightDrive = $rightDrive;

        parent::__construct(
            sprintf(
                'Drive specifiers %s and %s do not match.',
                var_export($leftDrive, true),
                var_export($rightDrive, true)
            ),
            0,
            $previous
        );
    }

    /**
     * Get the left-hand drive specifier.
     *
     * @return string The left-hand drive specifier.
     */
    public function leftDrive()
    {
        return $this->leftDrive;
    }

    /**
     * Get the right-hand drive specifier.
     *
     * @return string The right-hand drive specifier.
     */
    public function rightDrive()
    {
        return $this->rightDrive;
    }

    private $leftDrive;
    private $rightDrive;
}
