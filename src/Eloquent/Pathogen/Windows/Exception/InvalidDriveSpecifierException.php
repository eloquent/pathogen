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

final class InvalidDriveSpecifierException extends Exception
{
    /**
     * @param string         $drive
     * @param Exception|null $previous
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
     * Returns the invalid drive specifier.
     *
     * @return string
     */
    public function drive()
    {
        return $this->drive;
    }

    private $drive;
}
