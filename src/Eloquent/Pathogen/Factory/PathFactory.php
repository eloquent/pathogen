<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Factory;

use Eloquent\Pathogen\AbsolutePath;
use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use Eloquent\Pathogen\Normalizer\PathNormalizer;
use Eloquent\Pathogen\Normalizer\PathNormalizerInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\RelativePath;

class PathFactory implements PathFactoryInterface
{
    /**
     * @param PathFactoryInterface|null $instance
     *
     * @return PathFactoryInterface
     */
    static public function get(PathFactoryInterface $instance = null)
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
     * @param PathFactoryInterface $instance
     */
    static public function install(PathFactoryInterface $instance)
    {
        static::$instance = $instance;
    }

    static public function uninstall()
    {
        static::$instance = null;
    }

    /**
     * @param PathNormalizerInterface|null $normalizer
     */
    public function __construct(PathNormalizerInterface $normalizer = null)
    {
        $this->normalizer = PathNormalizer::get($normalizer);
    }

    /**
     * @return PathNormalizerInterface
     */
    public function normalizer()
    {
        return $this->normalizer;
    }

    /**
     * Creates a new path instance from its string representation.
     *
     * @param string $path
     *
     * @return PathInterface
     */
    public function create($path)
    {
        $isAbsolute = false;
        $hasTrailingSeparator = false;

        $atoms = explode('/', $path);
        $numAtoms = count($atoms);

        if ($numAtoms > 1) {
            if ('' === $atoms[0]) {
                $isAbsolute = true;
                array_shift($atoms);
                --$numAtoms;
            }

            if ('' === $atoms[$numAtoms - 1]) {
                $hasTrailingSeparator = !$isAbsolute || $numAtoms > 1;
                array_pop($atoms);
                --$numAtoms;
            }
        }

        foreach ($atoms as $index => $atom) {
            if ('' === $atom) {
                array_splice($atoms, $index, 1);
                --$numAtoms;
            }
        }

        if (!$isAbsolute && $numAtoms < 1) {
            $atoms = array('.');
        }

        return $this->createFromAtoms($atoms, $isAbsolute, $hasTrailingSeparator);
    }

    /**
     * Creates a new path instance from a set of path atoms.
     *
     * Unless otherwise specified, created paths will be absolute, and have no
     * trailing separator.
     *
     * @param mixed<string> $atoms
     * @param boolean|null  $isAbsolute
     * @param boolean|null  $hasTrailingSeparator
     *
     * @return PathInterface
     * @throws InvalidPathAtomExceptionInterface If any supplied atom is invalid.
     */
    public function createFromAtoms($atoms, $isAbsolute = null, $hasTrailingSeparator = null)
    {
        if ($isAbsolute) {
            return new AbsolutePath($atoms, $hasTrailingSeparator, $this, $this->normalizer());
        }

        return new RelativePath($atoms, $hasTrailingSeparator, $this, $this->normalizer());
    }

    static private $instance;
    private $normalizer;
}
