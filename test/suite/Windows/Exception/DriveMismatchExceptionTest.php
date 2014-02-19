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

class DriveMismatchExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $previous = new Exception;
        $exception = new DriveMismatchException('A', 'B', $previous);

        $this->assertSame(
            "Drive specifiers 'A' and 'B' do not match.",
            $exception->getMessage()
        );
        $this->assertSame('A', $exception->leftDrive());
        $this->assertSame('B', $exception->rightDrive());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
