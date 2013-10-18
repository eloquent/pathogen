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
use Eloquent\Pathogen\AbstractPath;
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
     * Normalize the supplied path to its most canonical form.
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
     * Normalize the supplied absolute path.
     *
     * @param AbsolutePathInterface $path The path to normalize.
     *
     * @return AbsolutePathInterface The normalized path.
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
     * Normalize the supplied relative path.
     *
     * @param RelativePathInterface $path The path to normalize.
     *
     * @return RelativePathInterface The normalized path.
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
     * Normalize the supplied path atoms for an absolute path.
     *
     * @param array<string> $atoms The path atoms to normalize.
     *
     * @return array<string> The normalized path atoms.
     */
    protected function normalizeAbsolutePathAtoms(array $atoms)
    {
        $resultingAtoms = array();
        foreach ($atoms as $atom) {
            if (AbstractPath::PARENT_ATOM === $atom) {
                array_pop($resultingAtoms);
            } elseif (AbstractPath::SELF_ATOM !== $atom) {
                $resultingAtoms[] = $atom;
            }
        }

        return $resultingAtoms;
    }

    /**
     * Normalize the supplied path atoms for a relative path.
     *
     * @param array<string> $atoms The path atoms to normalize.
     *
     * @return array<string> The normalized path atoms.
     */
    protected function normalizeRelativePathAtoms(array $atoms)
    {
        $resultingAtoms = array();
        $resultingAtomsCount = 0;
        $numAtoms = count($atoms);

        for ($i = 0; $i < $numAtoms; $i++) {
            if (AbstractPath::SELF_ATOM !== $atoms[$i]) {
                $resultingAtoms[] = $atoms[$i];
                $resultingAtomsCount++;
            }

            if (
                $resultingAtomsCount > 1 &&
                AbstractPath::PARENT_ATOM === $resultingAtoms[$resultingAtomsCount - 1] &&
                AbstractPath::PARENT_ATOM !== $resultingAtoms[$resultingAtomsCount - 2]
            ) {
                array_splice($resultingAtoms, $resultingAtomsCount - 2, 2);
                $resultingAtomsCount -= 2;
            }
        }

        if (count($resultingAtoms) < 1) {
            $resultingAtoms = array(AbstractPath::SELF_ATOM);
        }

        return $resultingAtoms;
    }

    private $factory;
}
