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
 * A supplied path atom contains a path separator.
 */
final class PathAtomContainsSeparatorException
    extends AbstractInvalidPathAtomException
{
    /**
     * Get the reason message.
     *
     * @return string The reason message.
     */
    public function reason()
    {
        return 'Path atoms must not contain separators.';
    }
}
