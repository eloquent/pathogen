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
use Eloquent\Pathogen\PathInterface;

class BoundPathResolver implements BoundPathResolverInterface
{
    /**
     * @param AbsolutePathInterface      $basePath
     * @param PathResolverInterface|null $resolver
     */
    public function __construct(
        AbsolutePathInterface $basePath,
        PathResolverInterface $resolver = null
    ) {
        if (null === $resolver) {
            $resolver = new PathResolver;
        }

        $this->basePath = $basePath;
        $this->resolver = $resolver;
    }

    /**
     * @returns AbsolutePathInterface
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * @returns PathResolverInterface
     */
    public function resolver()
    {
        return $this->resolver;
    }

    /**
     * @param PathInterface $path
     *
     * @return AbsolutePathInterface
     */
    public function resolve(PathInterface $path)
    {
        return $this->resolver()->resolve($this->basePath(), $path);
    }

    private $basePath;
    private $resolver;
}
