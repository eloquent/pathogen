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
     * @param mixed<string> $atoms
     * @param boolean|null  $hasTrailingSeparator
     *
     * @throws Exception\InvalidPathAtomExceptionInterface
     */
    public function __construct($atoms, $hasTrailingSeparator = null)
    {
        if (null === $hasTrailingSeparator) {
            $hasTrailingSeparator = false;
        }

        $this->atoms = $this->normalizeAtoms($atoms);
        $this->hasTrailingSeparator = $hasTrailingSeparator === true;
    }

    // Implementation of PathInterface =========================================

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
     * Returns a string representation of this path.
     *
     * @return string
     */
    public function string()
    {
        return sprintf(
            '%s%s',
            implode(static::ATOM_SEPARATOR, $this->atoms()),
            $this->hasTrailingSeparator() ? static::ATOM_SEPARATOR : ''
        );
    }

    /**
     * Returns a string representation of this path.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->string();
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

        if ($numAtoms > 0) {
            return $atoms[$numAtoms - 1];
        }

        return '';
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
        $parts = explode(static::EXTENSION_SEPARATOR, $this->name());
        if (count($parts) > 1) {
            array_pop($parts);

            return implode(static::EXTENSION_SEPARATOR, $parts);
        }

        return $parts[0];
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
        $parts = explode(static::EXTENSION_SEPARATOR, $this->name());

        return $parts[0];
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
        $parts = explode(static::EXTENSION_SEPARATOR, $this->name());
        if (count($parts) > 1) {
            array_shift($parts);

            return implode(static::EXTENSION_SEPARATOR, $parts);
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
        $parts = explode(static::EXTENSION_SEPARATOR, $this->name());
        $numParts = count($parts);

        if ($numParts > 1) {
            return $parts[$numParts - 1];
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
        return count(explode(static::EXTENSION_SEPARATOR, $this->name())) > 1;
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

        return $this->createPath(
            $this->atoms(),
            $this instanceof AbsolutePathInterface
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
        $parts = explode(static::EXTENSION_SEPARATOR, $this->name());

        if ($numAtoms === 0 || count($parts) < 2) {
            return $this;
        }

        array_pop($parts);

        $atoms[$numAtoms - 1] = implode(static::EXTENSION_SEPARATOR, $parts);

        return $this->createPath(
            $atoms,
            $this instanceof AbsolutePathInterface
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
        $parts = explode(static::EXTENSION_SEPARATOR, $this->name());

        if ($numAtoms === 0 || count($parts) < 2) {
            return $this;
        }

        $atoms[$numAtoms - 1] = $parts[0];

        return $this->createPath(
            $atoms,
            $this instanceof AbsolutePathInterface
        );
    }

    /**
     * Returns a new path with the supplied atom(s) suffixed to this path.
     *
     * @param string     $atom
     * @param string,... $additionalAtoms
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If any joined atoms
     * are invalid.
     */
    public function joinAtoms($atom)
    {
        return $this->joinAtomSequence(func_get_args());
    }

    /**
     * Returns a new path with the supplied sequence of atoms suffixed to this
     * path.
     *
     * @param mixed<string> $atoms
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If any joined atoms
     * are invalid.
     */
    public function joinAtomSequence($atoms)
    {
        if (!is_array($atoms)) {
            $atoms = iterator_to_array($atoms);
        }

        return $this->createPath(
            array_merge($this->atoms(), $atoms),
            $this instanceof AbsolutePathInterface
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
        return $this->joinAtomSequence($path->atoms());
    }

    /**
     * Returns a new path instance with the supplied extensions suffixed to this
     * path.
     *
     * @param string     $extension
     * @param string,... $additionalExtensions
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If the suffixed
     * extensions cause the atom to be invalid.
     */
    public function joinExtensions($extension)
    {
        return $this->joinExtensionSequence(func_get_args());
    }

    /**
     * Returns a new path instance with the supplied extensions suffixed to this
     * path.
     *
     * @param mixed<string> $extensions
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If the suffixed
     * extensions cause the atom to be invalid.
     */
    public function joinExtensionSequence($extensions)
    {
        if (!is_array($extensions)) {
            $extensions = iterator_to_array($extensions);
        }

        $resultingAtoms = $this->atoms();
        $name = $this->name();
        if ('' === $name) {
            $resultingAtoms[] = sprintf(
                '%s%s',
                static::EXTENSION_SEPARATOR,
                implode(static::EXTENSION_SEPARATOR , $extensions)
            );
        } else {
            $resultingAtoms[count($resultingAtoms) - 1] = sprintf(
                '%s%s%s',
                $name,
                static::EXTENSION_SEPARATOR,
                implode(static::EXTENSION_SEPARATOR , $extensions)
            );
        }

        return $this->createPath(
            $resultingAtoms,
            $this instanceof AbsolutePathInterface
        );
    }

    /**
     * Returns a new path instance with the supplied string suffixed to the last
     * path atom.
     *
     * @param string $suffix
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If the suffix causes
     * the atom to be invalid.
     */
    public function suffixName($suffix)
    {
        $atoms = $this->atoms();
        $atoms[count($atoms) - 1] = $this->name() . $suffix;

        return $this->createPath(
            $atoms,
            $this instanceof AbsolutePathInterface
        );
    }

    /**
     * Returns a new path instance with the supplied string prefixed to the last
     * path atom.
     *
     * @param string $prefix
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If the prefix causes
     * the atom to be invalid.
     */
    public function prefixName($prefix)
    {
        $atoms = $this->atoms();
        $atoms[count($atoms) - 1] = sprintf('%s%s', $prefix, $this->name());

        return $this->createPath(
            $atoms,
            $this instanceof AbsolutePathInterface
        );
    }

    // Implementation details ==================================================

    /**
     * @param mixed<string> $atoms
     *
     * @return array<string>
     */
    protected function normalizeAtoms($atoms)
    {
        $normalizedAtoms = array();
        foreach ($atoms as $atom) {
            $this->validateAtom($atom);
            $normalizedAtoms[] = $atom;
        }

        return $normalizedAtoms;
    }

    /**
     * @param string $atom
     */
    protected function validateAtom($atom)
    {
        if ('' === $atom) {
            throw new Exception\EmptyPathAtomException;
        } elseif (false !== strpos($atom, static::ATOM_SEPARATOR)) {
            throw new Exception\PathAtomContainsSeparatorException($atom);
        }
    }

    /**
     * @param mixed<string> $atoms
     * @param boolean       $isAbsolute
     * @param boolean|null  $hasTrailingSeparator
     *
     * @return PathInterface
     */
    protected function createPath(
        $atoms,
        $isAbsolute,
        $hasTrailingSeparator = null
    ) {
        if ($isAbsolute) {
            return new AbsolutePath(
                $atoms,
                $hasTrailingSeparator
            );
        }

        return new RelativePath(
            $atoms,
            $hasTrailingSeparator
        );
    }

    private $atoms;
    private $hasTrailingSeparator;
}
