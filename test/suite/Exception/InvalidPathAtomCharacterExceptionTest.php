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
 * @covers Eloquent\Pathogen\Exception\InvalidPathAtomCharacterException
 * @covers Eloquent\Pathogen\Exception\AbstractInvalidPathAtomException
 */
class InvalidPathAtomCharacterExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $previous = new Exception;
        $exception = new InvalidPathAtomCharacterException('foobar', 'b', $previous);

        $this->assertSame(
            "Invalid path atom 'foobar'. Path atom contains invalid character 'b'.",
            $exception->getMessage()
        );
        $this->assertSame(
            "Path atom contains invalid character 'b'.",
            $exception->reason()
        );
        $this->assertSame('foobar', $exception->atom());
        $this->assertSame('b', $exception->character());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
