<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows\Exception;

use Exception;

final class DriveMismatchException extends Exception
{
    /**
     * @param string         $leftDrive
     * @param string         $rightDrive
     * @param Exception|null $previous
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
     * Returns the left-hand drive specifier.
     *
     * @return string
     */
    public function leftDrive()
    {
        return $this->leftDrive;
    }

    /**
     * Returns the right-hand drive specifier.
     *
     * @return string
     */
    public function rightDrive()
    {
        return $this->rightDrive;
    }

    private $leftDrive;
    private $rightDrive;
}
