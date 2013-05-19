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
use Eloquent\Pathogen\AbstractPath;
use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use Eloquent\Pathogen\Normalizer\PathNormalizer;
use Eloquent\Pathogen\Normalizer\PathNormalizerInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\RelativePath;

class PathFactory implements PathFactoryInterface
{
    /**
     * @param PathNormalizerInterface|null $normalizer
     */
    public function __construct(PathNormalizerInterface $normalizer = null)
    {
        if (null === $normalizer) {
            $normalizer = new PathNormalizer($this);
        }

        $this->normalizer = $normalizer;
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

        $atoms = explode(AbstractPath::ATOM_SEPARATOR, $path);
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

        return $this->createFromAtoms(
            $atoms,
            $isAbsolute,
            $hasTrailingSeparator
        );
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
     * @throws InvalidPathAtomExceptionInterface If any supplied atom is
     * invalid.
     */
    public function createFromAtoms(
        $atoms,
        $isAbsolute = null,
        $hasTrailingSeparator = null
    ) {
        if ($isAbsolute) {
            return new AbsolutePath(
                $atoms,
                $hasTrailingSeparator,
                $this,
                $this->normalizer()
            );
        }

        return new RelativePath(
            $atoms,
            $hasTrailingSeparator,
            $this,
            $this->normalizer()
        );
    }

    private $normalizer;
}
