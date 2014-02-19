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

/**
 * The supplied path is not absolute.
 */
final class NonAbsolutePathException extends AbstractInvalidPathException
{
    /**
     * Get the reason message.
     *
     * @return string The reason message.
     */
    public function reason()
    {
        return 'The supplied path is not absolute.';
    }
}
