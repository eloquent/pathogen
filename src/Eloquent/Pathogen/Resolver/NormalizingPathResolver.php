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

class NormalizingPathResolver implements PathResolverInterface
{
    /**
     * @param PathNormalizerInterface|null $normalizer
     * @param PathResolverInterface|null   $resolver
     */
    public function __construct(
        PathNormalizerInterface $normalizer = null,
        PathResolverInterface $resolver = null
    ) {
        if (null === $normalizer) {
            $normalizer = new PathNormalizer;
        }
        if (null === $resolver) {
            $resolver = new PathResolver;
        }

        $this->normalizer = $normalizer;
        $this->resolver = $resolver;
    }

    /**
     * @return PathNormalizerInterface
     */
    public function normalizer()
    {
        return $this->normalizer;
    }

    /**
     * @return PathResolverInterface
     */
    public function resolver()
    {
        return $this->resolver;
    }

    /**
     * @param AbsolutePathInterface $basePath
     * @param PathInterface         $path
     *
     * @return AbsolutePathInterface
     */
    public function resolve(
        AbsolutePathInterface $basePath,
        PathInterface $path
    ) {
        return $this->normalizer()->normalize(
            $this->resolver()->resolve($basePath, $path)
        );
    }

    private $normalizer;
    private $resolver;
}
