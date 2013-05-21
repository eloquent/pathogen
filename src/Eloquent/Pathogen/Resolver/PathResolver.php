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

class PathResolver implements PathResolverInterface
{
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
        if ($path instanceof AbsolutePathInterface) {
            return $path;
        }

        return $basePath->join($path);
    }
}
