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
use PHPUnit_Framework_TestCase;

class InvalidPathStateExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $previous = new Exception;
        $exception = new InvalidPathStateException('Foo.', $previous);

        $this->assertSame(
            "Invalid path state. Foo.",
            $exception->getMessage()
        );
        $this->assertSame('Foo.', $exception->reason());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
