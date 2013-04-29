<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Normalizer;

use Eloquent\Pathogen\PathInterface;

class PathNormalizer implements PathNormalizerInterface
{
	/**
     * @param PathNormalizerInterface|null $instance
     *
     * @return PathNormalizerInterface
     */
    static public function get(PathNormalizerInterface $instance = null)
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
     * @param PathNormalizerInterface $instance
     */
    static public function install(PathNormalizerInterface $instance)
    {
        static::$instance = $instance;
    }

    static public function uninstall()
    {
        static::$instance = null;
    }

    /**
     * @param PathInterface $path
     *
     * @return PathInterface
     */
    public function normalize(PathInterface $path)
    {
    	return $path;
    }

    static private $instance;
}
