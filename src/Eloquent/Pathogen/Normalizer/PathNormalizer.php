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

class PathNormalizer implements PathNormalizerInterface
{
    /**
     * @param PathFactoryInterface|null $factory
     */
    public function __construct(PathFactoryInterface $factory = null)
    {
        if (null === $factory) {
            $factory = new PathFactory($this);
        }

        $this->factory = $factory;
    }

    /**
     * @return PathFactoryInterface
     */
    public function factory()
    {
        return $this->factory;
    }

    /**
     * @param PathInterface $path
     *
     * @return PathInterface
     */
    public function normalize(PathInterface $path)
    {
        return $path instanceof AbsolutePathInterface
            ? $this->normalizeAbsolute($path)
            : $this->normalizeRelative($path);
    }

    /**
     * @param AbsolutePathInterface $path
     *
     * @return AbsolutePathInterface
     */
    protected function normalizeAbsolute(AbsolutePathInterface $path)
    {
        $resultingAtoms = array();
        $atoms = $path->atoms();
        foreach ($atoms as $atom) {
            if (AbstractPath::PARENT_ATOM === $atom) {
                array_pop($resultingAtoms);
            } elseif (AbstractPath::SELF_ATOM !== $atom) {
                $resultingAtoms[] = $atom;
            }
        }

        return $this->factory()->createFromAtoms(
            $resultingAtoms,
            true,
            false
        );
    }

    /**
     * @param RelativePathInterface $path
     *
     * @return RelativePathInterface
     */
    protected function normalizeRelative(RelativePathInterface $path)
    {
        $resultingAtoms = array();
        $resultingAtomsCount = 0;
        $atoms = $path->atoms();
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

        return $this->factory()->createFromAtoms(
            $resultingAtoms,
            false,
            false
        );
    }

    private $factory;
}
