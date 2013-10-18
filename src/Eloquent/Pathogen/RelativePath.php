<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen;

/**
 * Represents a relative path.
 */
class RelativePath extends AbstractPath implements RelativePathInterface
{
    // Implementation of PathInterface =========================================

    /**
     * Get an absolute version of this path.
     *
     * If this path is relative, a new absolute path with equivalent atoms will
     * be returned. Otherwise, this path will be retured unaltered.
     *
     * @return AbsolutePathInterface An absolute version of this path.
     */
    public function toAbsolute()
    {
        return $this->createPath(
            $this->atoms(),
            true,
            $this->hasTrailingSeparator()
        );
    }

    /**
     * Get a relative version of this path.
     *
     * If this path is absolute, a new relative path with equivalent atoms will
     * be returned. Otherwise, this path will be retured unaltered.
     *
     * @return RelativePathInterface        A relative version of this path.
     * @throws Exception\EmptyPathException If this path has no atoms.
     */
    public function toRelative()
    {
        return $this;
    }

    // Implementation of RelativePathInterface =================================

    /**
     * Determine whether this path is the self path.
     *
     * The self path is a relative path with a single self atom (i.e. a dot
     * '.').
     *
     * @param Normalizer\PathNormalizerInterface|null $normalizer The normalizer to use when determining the result.
     *
     * @return boolean True if this path is the self path.
     */
    public function isSelf(
        Normalizer\PathNormalizerInterface $normalizer = null
    ) {
        if (null === $normalizer) {
            $normalizer = $this->createDefaultNormalizer();
        }

        $atoms = $this->normalize($normalizer)->atoms();

        return 1 === count($atoms) && static::SELF_ATOM === $atoms[0];
    }

    // Implementation details ==================================================

    /**
     * Normalizes and validates a sequence of path atoms.
     *
     * This method is called internally by the constructor upon instantiation.
     * It can be overridden in child classes to change how path atoms are
     * normalized and/or validated.
     *
     * @param mixed<string> $atoms The path atoms to normalize.
     *
     * @return array<string>                                The normalized path atoms.
     * @throws Exception\EmptyPathAtomException             If any path atom is empty.
     * @throws Exception\PathAtomContainsSeparatorException If any path atom contains a separator.
     */
    protected function normalizeAtoms($atoms)
    {
        $atoms = parent::normalizeAtoms($atoms);
        if (count($atoms) < 1) {
            throw new Exception\EmptyPathException;
        }

        return $atoms;
    }
}
