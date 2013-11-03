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

use Eloquent\Pathogen\AbsolutePathInterface;
use Eloquent\Pathogen\Normalizer\PathNormalizer;
use Eloquent\Pathogen\Normalizer\PathNormalizerInterface;
use Eloquent\Pathogen\PathInterface;

/**
 * A path resolver that wraps another path resolver and automatically normalizes
 * the result.
 */
class NormalizingPathResolver implements PathResolverInterface
{
    /**
     * Construct a new normalizing path resolver.
     *
     * @param PathNormalizerInterface|null $normalizer The path normalizer to use.
     * @param PathResolverInterface|null   $resolver   The path resolver to use.
     */
    public function __construct(
        PathNormalizerInterface $normalizer = null,
        PathResolverInterface $resolver = null
    ) {
        if (null === $normalizer) {
            $normalizer = PathNormalizer::instance();
        }
        if (null === $resolver) {
            $resolver = PathResolver::instance();
        }

        $this->normalizer = $normalizer;
        $this->resolver = $resolver;
    }

    /**
     * Get the path normalizer used by this resolver.
     *
     * @return PathNormalizerInterface The path normalizer.
     */
    public function normalizer()
    {
        return $this->normalizer;
    }

    /**
     * Get the resolver used internally by this resolver.
     *
     * @returns PathResolverInterface The inner path resolver.
     */
    public function resolver()
    {
        return $this->resolver;
    }

    /**
     * Resolve a path against a given base path.
     *
     * @param AbsolutePathInterface $basePath The base path.
     * @param PathInterface         $path     The path to resolve.
     *
     * @return AbsolutePathInterface The resolved path.
     */
    public function resolve(
        AbsolutePathInterface $basePath,
        PathInterface $path
    ) {
        return $this->resolver()
            ->resolve($basePath, $path)
            ->normalize($this->normalizer());
    }

    private $normalizer;
    private $resolver;
}
