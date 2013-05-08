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

final class PathAtomContainsSeparatorException
    extends AbstractInvalidPathAtomException
{
    /**
     * @return string
     */
    public function reason()
    {
        return 'Path atoms must not contain separators.';
    }
}
