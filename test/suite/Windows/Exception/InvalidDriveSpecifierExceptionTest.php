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
use PHPUnit_Framework_TestCase;

class InvalidDriveSpecifierExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $previous = new Exception;
        $exception = new InvalidDriveSpecifierException('$', $previous);

        $this->assertSame(
            "Invalid drive specifier '$'.",
            $exception->getMessage()
        );
        $this->assertSame('$', $exception->drive());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
