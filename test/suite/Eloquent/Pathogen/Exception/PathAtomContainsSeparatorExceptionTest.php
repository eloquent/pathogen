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

/**
 * @covers Eloquent\Pathogen\Exception\AbstractInvalidPathAtomException
 * @covers Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException
 */
class PathAtomContainsSeparatorExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $previous = new Exception;
        $exception = new PathAtomContainsSeparatorException(
            'foo/bar',
            $previous
        );

        $this->assertSame(
            'Invalid path atom "foo/bar".'
            . ' Path atoms must not contain separators.',
            $exception->getMessage()
        );
        $this->assertSame(
            'Path atoms must not contain separators.',
            $exception->reason()
        );
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame('foo/bar', $exception->atom());
    }
}
