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

abstract class AbstractPath implements PathInterface
{
    /**
     * @param mixed<string>                           $atoms
     * @param boolean|null                            $hasTrailingSeparator
     * @param Factory\PathFactoryInterface|null       $factory
     * @param Normalizer\PathNormalizerInterface|null $normalizer
     */
    public function __construct(
        $atoms,
        $hasTrailingSeparator = null,
        Factory\PathFactoryInterface $factory = null,
        Normalizer\PathNormalizerInterface $normalizer = null
    ) {
        if (null === $hasTrailingSeparator) {
            $hasTrailingSeparator = false;
        }

        $this->atoms = array();
        foreach ($atoms as $atom) {
            if ('' === $atom) {
                throw new Exception\EmptyPathAtomException;
            } elseif (false !== strpos($atom, static::SEPARATOR)) {
                throw new Exception\PathAtomContainsSeparatorException($atom);
            }

            $this->atoms[] = $atom;
        }

        $this->hasTrailingSeparator = $hasTrailingSeparator;
        $this->factory = Factory\PathFactory::get($factory);
        $this->normalizer = Normalizer\PathNormalizer::get($normalizer);
    }

    /**
     * Returns the atoms of this path as an array of strings.
     *
     * For example, the path '/foo/bar' has the atoms 'foo' and 'bar'.
     *
     * @return mixed<integer,string>
     */
    public function atoms()
    {
        return $this->atoms;
    }

    /**
     * Returns true is at least one atom is present
     *
     * @return boolean
     */
    public function hasAtoms()
    {
        return count($this->atoms()) > 0;
    }

    /**
     * Returns true if this path ends with a path separator.
     *
     * @return boolean
     */
    public function hasTrailingSeparator()
    {
        return $this->hasTrailingSeparator;
    }

    /**
     * @return Factory\PathFactoryInterface
     */
    public function factory()
    {
        return $this->factory;
    }

    /**
     * @return Normalizer\PathNormalizerInterface
     */
    public function normalizer()
    {
        return $this->normalizer;
    }

    /**
     * Returns a string representation of this path.
     *
     * @return string
     */
    public function string()
    {
        return sprintf(
            '%s%s',
            implode('/', $this->atoms()),
            $this->hasTrailingSeparator() ? '/' : ''
        );
    }

    /**
     * Returns a string representation of this path.
     *
     * @return string
     */
    public function __toString()
    {

    }

    /**
     * Returns the last atom of this path.
     *
     * If this path has no atoms, an empty string is returned.
     *
     * @return string
     */
    public function name()
    {
        $atoms = $this->atoms();
        $numAtoms = count($atoms);

        return $numAtoms > 0 ? $atoms[$numAtoms - 1] : '';
    }

    /**
     * Returns the last atom of this path, excluding the last extension.
     *
     * If this path has no atoms, an empty string is returned.
     *
     * @return string
     */
    public function nameWithoutExtension()
    {
        if (false !== strpos($this->name(), '.')) {
            $parts = explode('.', $this->name());
            array_pop($parts);

            return implode('.', $parts);
        }

        return $this->name();
    }

    /**
     * Returns the last atom of this path, excluding any extensions.
     *
     * If this path has no atoms, an empty string is returned.
     *
     * @return string
     */
    public function namePrefix()
    {
        if (false !== strpos($this->name(), '.')) {
            $parts = explode('.', $this->name());

            return array_shift($parts);
        }

        return $this->name();
    }

    /**
     * Returns the extensions of this path's last atom.
     *
     * If the last atom has no extensions, or this path has no atoms, this
     * method will return null.
     *
     * @return string|null
     */
    public function nameSuffix()
    {
        if (false !== strpos($this->name(), '.')) {
            $parts = explode('.', $this->name());
            array_shift($parts);

            return implode('.', $parts);
        }

        return null;
    }

    /**
     * Returns the last extension of this path's last atom.
     *
     * If the last atom has no extensions, or this path has no atoms, this
     * method will return null.
     *
     * @return string|null
     */
    public function extension()
    {
        if (false !== strpos($this->name(), '.')) {
            $parts = explode('.', $this->name());
            return end($parts);
        }

        return null;
    }

    /**
     * Returns true if this path's last atom has any extensions.
     *
     * @return boolean
     */
    public function hasExtension()
    {
        return false !== strpos($this->name(), '.');
    }

    /**
     * Returns a new path instance with the trailing slash removed from this
     * path.
     *
     * If this path has no trailing slash, the path is returned unmodified.
     *
     * @return PathInterface
     */
    public function stripTrailingSlash()
    {
        if (!$this->hasTrailingSeparator()) {
            return $this;
        }

        return $this->factory()->createFromAtoms(
            $this->atoms(),
            $this instanceof AbsolutePathInterface,
            false
        );
    }

    /**
     * Returns a new path instance with the last extension removed from this
     * path.
     *
     * If this path has no extensions, the path is returned unmodified.
     *
     * @return PathInterface
     */
    public function stripExtension()
    {
        $atoms = $this->atoms();
        $numAtoms = count($atoms);

        if ($numAtoms === 0 || false === strpos($this->name(), '.')) {
            return $this;
        }

        $parts = explode('.', $this->name());
        array_pop($parts);

        $atoms[$numAtoms - 1] = implode('.', $parts);

        return $this->factory()->createFromAtoms(
            $atoms,
            $this instanceof AbsolutePathInterface,
            $this->hasTrailingSeparator()
        );
    }

    /**
     * Returns a new path instance with all extensions removed from this path.
     *
     * If this path has no extensions, the path is returned unmodified.
     *
     * @return PathInterface
     */
    public function stripNameSuffix()
    {
        $atoms = $this->atoms();
        $numAtoms = count($atoms);

        if (count($atoms) === 0 || false === strpos($this->name(), '.')) {
            return $this;
        }

        $parts = explode('.', $this->name());
        $name = array_shift($parts);

        $atoms[$numAtoms - 1] = $name;

        return $this->factory()->createFromAtoms(
            $atoms,
            $this instanceof AbsolutePathInterface,
            $this->hasTrailingSeparator()
        );
    }

    /**
     * Returns a new path with the supplied atom(s) suffixed to this path.
     *
     * @param string     $atom
     * @param string,... $additionalAtoms
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If any joined atoms are invalid.
     */
    public function joinAtoms($atom)
    {
        $atoms = func_get_args();

        return $this->joinAtomSequence($atoms);
    }

    /**
     * Returns a new path with the supplied sequence of atoms suffixed to this
     * path.
     *
     * @param mixed<string> $atoms
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If any joined atoms are invalid.
     */
    public function joinAtomSequence($atoms)
    {
        $resultingAtoms = $this->atoms();

        foreach ($atoms as $atom) {
            $resultingAtoms[] = $atom;
        }

        return $this->factory()->createFromAtoms(
            $resultingAtoms,
            $this instanceof AbsolutePathInterface,
            false
        );
    }

    /**
     * Returns a new path with the supplied path suffixed to this path.
     *
     * @param RelativePathInterface $path
     *
     * @return PathInterface
     */
    public function join(RelativePathInterface $path)
    {
        return $this->factory()->createFromAtoms(
            array_merge($this->atoms(), $path->atoms()),
            $this instanceof AbsolutePathInterface,
            false
        );
    }

    /**
     * Returns a new path instance with a trailing slash suffixed to this path.
     *
     * @return PathInterface
     */
    public function joinTrailingSlash()
    {
        if ($this->hasTrailingSeparator() || !$this->hasAtoms()) {
            return $this;
        }

        return $this->factory()->createFromAtoms(
            $this->atoms(),
            $this instanceof AbsolutePathInterface,
            true
        );
    }

    /**
     * Returns a new path instance with the supplied extensions suffixed to this
     * path.
     *
     * @param string     $extension
     * @param string,... $additionalExtensions
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If the suffixed extensions cause the atom to be invalid.
     */
    public function joinExtensions($extension)
    {
        $extensions = func_get_args();

        return $this->joinExtensionSequence($extensions);
    }

    /**
     * Returns a new path instance with the supplied extensions suffixed to this
     * path.
     *
     * @param mixed<string> $extensions
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If the suffixed extensions cause the atom to be invalid.
     */
    public function joinExtensionSequence($extensions)
    {
        // TODO: Throw exception
        $resultingExtensions = array();
        foreach ($extensions as $extension) {
            $resultingExtensions[] = $extension;
        }

        $resultingAtoms = $this->atoms();
        $resultingAtoms[count($resultingAtoms) - 1] = $this->name() . '.' . implode('.' , $resultingExtensions);

        return $this->factory()->createFromAtoms(
            $resultingAtoms,
            $this instanceof AbsolutePathInterface,
            false
        );
    }

    /**
     * Returns a new path instance with the supplied string suffixed to the last
     * path atom.
     *
     * @param string $suffix
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If the suffix causes the atom to be invalid.
     */
    public function suffixName($suffix)
    {
        // TODO: Throw exception
        $atoms = $this->atoms();
        $atoms[count($atoms) - 1] = $this->name() . $suffix;

        return $this->factory()->createFromAtoms(
            $atoms,
            $this instanceof AbsolutePathInterface,
            false
        );
    }

    /**
     * Returns a new path instance with the supplied string prefixed to the last
     * path atom.
     *
     * @param string $prefix
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If the prefix causes the atom to be invalid.
     */
    public function prefixName($prefix)
    {
        // TODO: Throw exception
        $atoms = $this->atoms();
        $atoms[count($atoms) - 1] = $prefix . $this->name();

        return $this->factory()->createFromAtoms(
            $atoms,
            $this instanceof AbsolutePathInterface,
            false
        );
    }

    private $atoms;
    private $hasTrailingSeparator;
    private $factory;
    private $normalizer;
}
