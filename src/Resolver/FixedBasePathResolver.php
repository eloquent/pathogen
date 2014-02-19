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

use Eloquent\Pathogen\AbsolutePathInterface;
use Eloquent\Pathogen\PathInterface;

/**
 * A path resolver that wraps another path resolver with a fixed base path.
 */
class FixedBasePathResolver implements PathResolverInterface
{
    /**
     * Construct a new fixed base path resolver.
     *
     * @param AbsolutePathInterface          $basePath The base path.
     * @param BasePathResolverInterface|null $resolver The base path resolver to use.
     */
    public function __construct(
        AbsolutePathInterface $basePath,
        BasePathResolverInterface $resolver = null
    ) {
        if (null === $resolver) {
            $resolver = BasePathResolver::instance();
        }

        $this->basePath = $basePath;
        $this->resolver = $resolver;
    }

    /**
     * Get the base path used by this resolver.
     *
     * @returns AbsolutePathInterface The base path.
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Get the base path resolver used internally by this resolver.
     *
     * @returns BasePathResolverInterface The base path resolver.
     */
    public function resolver()
    {
        return $this->resolver;
    }

    /**
     * Resolve a path against the base path.
     *
     * @param PathInterface $path The path to resolve.
     *
     * @return AbsolutePathInterface The resolved path.
     */
    public function resolve(PathInterface $path)
    {
        return $this->resolver()->resolve($this->basePath(), $path);
    }

    private $basePath;
    private $resolver;
}
