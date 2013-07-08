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

use Eloquent\Pathogen\AbsolutePathInterface;
use Eloquent\Pathogen\Factory\PathFactory;
use Eloquent\Pathogen\Factory\PathFactoryInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\RelativePathInterface;

/**
 * A path normalizer suitable for generic, Unix-style path instances.
 */
class PathNormalizer implements PathNormalizerInterface
{
    /**
     * Construct a new path normalizer.
     *
     * @param PathFactoryInterface|null $factory The path factory to use.
     */
    public function __construct(PathFactoryInterface $factory = null)
    {
        if (null === $factory) {
            $factory = new PathFactory;
        }

        $this->factory = $factory;
    }

    /**
     * Get the path factory used by this normalizer.
     *
     * @return PathFactoryInterface The path factory.
     */
    public function factory()
    {
        return $this->factory;
    }

    /**
     * Normalize the supplied path to it's most canonical form.
     *
     * @param PathInterface $path The path to normalize.
     *
     * @return PathInterface The normalized path.
     */
    public function normalize(PathInterface $path)
    {
        if ($path instanceof AbsolutePathInterface) {
            return $this->normalizeAbsolutePath($path);
        }

        return $this->normalizeRelativePath($path);
    }

    /**
     * @param AbsolutePathInterface $path
     *
     * @return AbsolutePathInterface
     */
    protected function normalizeAbsolutePath(AbsolutePathInterface $path)
    {
        return $this->factory()->createFromAtoms(
            $this->normalizeAbsolutePathAtoms($path->atoms()),
            true,
            false
        );
    }

    /**
     * @param RelativePathInterface $path
     *
     * @return RelativePathInterface
     */
    protected function normalizeRelativePath(RelativePathInterface $path)
    {
        return $this->factory()->createFromAtoms(
            $this->normalizeRelativePathAtoms($path->atoms()),
            false,
            false
        );
    }

    /**
     * @param array<string> $atoms
     *
     * @return array<string>
     */
    protected function normalizeAbsolutePathAtoms(array $atoms)
    {
        $resultingAtoms = array();
        foreach ($atoms as $atom) {
            if (PathInterface::PARENT_ATOM === $atom) {
                array_pop($resultingAtoms);
            } elseif (PathInterface::SELF_ATOM !== $atom) {
                $resultingAtoms[] = $atom;
            }
        }

        return $resultingAtoms;
    }

    /**
     * @param array<string> $atoms
     *
     * @return array<string>
     */
    protected function normalizeRelativePathAtoms(array $atoms)
    {
        $resultingAtoms = array();
        $resultingAtomsCount = 0;
        $numAtoms = count($atoms);

        for ($i = 0; $i < $numAtoms; $i++) {
            if (PathInterface::SELF_ATOM !== $atoms[$i]) {
                $resultingAtoms[] = $atoms[$i];
                $resultingAtomsCount++;
            }

            if (
                $resultingAtomsCount > 1 &&
                PathInterface::PARENT_ATOM === $resultingAtoms[$resultingAtomsCount - 1] &&
                PathInterface::PARENT_ATOM !== $resultingAtoms[$resultingAtomsCount - 2]
            ) {
                array_splice($resultingAtoms, $resultingAtomsCount - 2, 2);
                $resultingAtomsCount -= 2;
            }
        }

        if (count($resultingAtoms) < 1) {
            $resultingAtoms = array(PathInterface::SELF_ATOM);
        }

        return $resultingAtoms;
    }

    private $factory;
}
