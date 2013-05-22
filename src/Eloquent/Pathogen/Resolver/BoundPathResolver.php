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
     * @param AbsolutePathInterface $basePath
     */
    public function __construct(AbsolutePathInterface $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @param PathInterface         $path
     *
     * @return AbsolutePathInterface
     */
    public function resolve(PathInterface $path, PathResolverInterface $resolver = null)
    {
        if ($path instanceof AbsolutePathInterface) {
            return $path;
        }

        if (null === $resolver) {
            $resolver = new PathResolver;
        }

        return $resolver->resolve($this->basePath(), $path);
    }

    /**
     * @returns AbsolutePathInterface
     */
    public function basePath()
    {
        return $this->basePath;
    }

    private $basePath;
}
