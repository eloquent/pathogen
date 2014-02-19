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
 * Interface for exceptions that handle invalid path atom cases.
 */
interface InvalidPathAtomExceptionInterface
{
    /**
     * Get the invalid path atom.
     *
     * @return string The invalid path atom.
     */
    public function atom();

    /**
     * Get the reason message.
     *
     * @return string The reason message.
     */
    public function reason();
}
