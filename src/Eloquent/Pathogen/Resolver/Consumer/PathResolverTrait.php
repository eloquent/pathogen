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

use Eloquent\Pathogen\Resolver\PathResolver;
use Eloquent\Pathogen\Resolver\PathResolverInterface;

/**
 * A trait for classes that take a path resolver as a dependency.
 */
trait PathResolverTrait
{
    /**
     * Set the path resolver.
     *
     * @param PathResolverInterface $pathResolver The path resolver to use.
     */
    public function setPathResolver(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    /**
     * Get the path resolver.
     *
     * @return PathResolverInterface The path resolver.
     */
    public function pathResolver()
    {
        if (null === $this->pathResolver) {
            $this->pathResolver = $this->createDefaultPathResolver();
        }

        return $this->pathResolver;
    }

    /**
     * Create a new instance of the default path resolver.
     *
     * @return PathResolverInterface The new default path resolver instance.
     */
    protected function createDefaultPathResolver()
    {
        return new PathResolver;
    }

    private $pathResolver;
}
