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

final class EmptyPathAtomException extends AbstractInvalidPathAtomException
{
    /**
     * @param Exception|null $previous
     */
    public function __construct(Exception $previous = null)
    {
        parent::__construct('', $previous);
    }

    /**
     * @return string
     */
    protected function reason()
    {
        return 'Path atoms must not be empty strings.';
    }
}
