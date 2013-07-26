<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Resolver\Consumer;

use Eloquent\Pathogen\Resolver\NormalizingPathResolver;

/**
 * A trait for classes that take a normalizing path resolver as a dependency.
 */
trait NormalizingPathResolverTrait
{
    use PathResolverTrait;

    /**
     * @return PathResolverInterface
     */
    protected function createDefaultPathResolver()
    {
        return new NormalizingPathResolver;
    }
}
