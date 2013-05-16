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
use Eloquent\Pathogen\Factory\PathFactory;

class PathResolver implements PathResolverInterface
{
    /**
     * @param PathResolverInterface|null $instance
     *
     * @return PathResolverInterface
     */
    public static function get(PathResolverInterface $instance = null)
    {
        if (null === $instance) {
            if (null === static::$instance) {
                static::install(new static);
            }

            $instance = static::$instance;
        }

        return $instance;
    }

    /**
     * @param PathResolverInterface $instance
     */
    public static function install(PathResolverInterface $instance)
    {
        static::$instance = $instance;
    }

    public static function uninstall()
    {
        static::$instance = null;
    }

    /**
     * @param AbsolutePathInterface $basePath
     * @param PathInterface         $path
     *
     * @return AbsolutePathInterface
     */
    public function resolve(AbsolutePathInterface $basePath, PathInterface $path)
    {
        $resultingPath = null;

        if ($path instanceof AbsolutePathInterface) {
            return $path;
        }

        $resultingPath = $basePath->parent()->join($path);


        return $resultingPath;
    }

    private static $instance;
}
