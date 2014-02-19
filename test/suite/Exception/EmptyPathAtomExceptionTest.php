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
use PHPUnit_Framework_TestCase;

/**
 * @covers Eloquent\Pathogen\Exception\EmptyPathAtomException
 * @covers Eloquent\Pathogen\Exception\AbstractInvalidPathAtomException
 */
class EmptyPathAtomExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $previous = new Exception;
        $exception = new EmptyPathAtomException($previous);

        $this->assertSame(
            "Invalid path atom ''. Path atoms must not be empty strings.",
            $exception->getMessage()
        );
        $this->assertSame(
            'Path atoms must not be empty strings.',
            $exception->reason()
        );
        $this->assertSame('', $exception->atom());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
