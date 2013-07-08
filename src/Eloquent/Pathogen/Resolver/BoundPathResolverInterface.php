<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Resolver;

use Eloquent\Pathogen\PathInterface;

/**
 * The interface implemented by path resolvers with a fixed base path.
 */
interface BoundPathResolverInterface
{
    /**
     * Resolve a path against the base path.
     *
     * @param PathInterface $path The path to resolve.
     *
     * @return AbsolutePathInterface The resolved path.
     */
    public function resolve(PathInterface $path);
}
