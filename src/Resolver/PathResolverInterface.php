<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Resolver;

use Eloquent\Pathogen\PathInterface;

/**
 * The interface implemented by path resolvers that do not require a base path.
 */
interface PathResolverInterface
{
    /**
     * Resolve the supplied path.
     *
     * @param PathInterface $path The path to resolve.
     *
     * @return AbsolutePathInterface The resolved path.
     */
    public function resolve(PathInterface $path);
}
