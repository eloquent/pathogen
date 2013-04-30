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
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\RelativePathInterface;

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
            if ('..' === $atom) {
                array_pop($resultingAtoms);
            } else if ('.' !== $atom) {
                $resultingAtoms[] = $atom;
            }
        }

        return PathFactory::get()->createFromAtoms($resultingAtoms, true, false);
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

        for ($loop = 0; $loop < $numAtoms; $loop++) {
            if ('.' !== $atoms[$loop]) {
                $resultingAtoms[] = $atoms[$loop];
                $resultingAtomsCount++;
            }

            if ($resultingAtomsCount > 1 && '..' === $resultingAtoms[$resultingAtomsCount - 1] && '..' !== $resultingAtoms[$resultingAtomsCount - 2]) {
                array_splice($resultingAtoms, $resultingAtomsCount - 2, 2);
                $resultingAtomsCount -= 2;
            }
        }

        return PathFactory::get()->createFromAtoms($resultingAtoms, false, false);
    }

    static private $instance;
}
