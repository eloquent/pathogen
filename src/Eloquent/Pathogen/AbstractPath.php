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
     * Returns true is at least one atom is present.
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
        return
            implode(static::ATOM_SEPARATOR, $this->atoms()) .
            ($this->hasTrailingSeparator() ? static::ATOM_SEPARATOR : '')
        ;
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
     * Returns the atoms of this path's name as an array of strings.
     *
     * For example, the path name 'foo.bar' has the atoms 'foo' and 'bar'.
     *
     * @return array<integer,string>
     */
    public function nameAtoms()
    {
        return explode(static::EXTENSION_SEPARATOR, $this->name());
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
        $atoms = $this->nameAtoms();
        if (count($atoms) > 1) {
            array_pop($atoms);

            return implode(static::EXTENSION_SEPARATOR, $atoms);
        }

        return $atoms[0];
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
        $atoms = $this->nameAtoms();

        return $atoms[0];
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
        $atoms = $this->nameAtoms();
        if (count($atoms) > 1) {
            array_shift($atoms);

            return implode(static::EXTENSION_SEPARATOR, $atoms);
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
        $atoms = $this->nameAtoms();
        $numParts = count($atoms);

        if ($numParts > 1) {
            return $atoms[$numParts - 1];
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
        return count($this->nameAtoms()) > 1;
    }

    /**
     * Returns the parent of this path.
     *
     * @return PathInterface
     */
    public function parent()
    {
        $atoms = $this->atoms();
        $atoms[] = static::PARENT_ATOM;

        return $this->createPath(
            $atoms,
            $this instanceof AbsolutePathInterface
        );
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
        return $this->replaceExtension(null);
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
        return $this->replaceNameSuffix(null);
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

        $atoms = $this->nameAtoms();
        if (array('', '') === $atoms) {
            array_pop($atoms);
        }

        return $this->replaceName(
            implode(
                static::EXTENSION_SEPARATOR,
                array_merge($atoms, $extensions)
            )
        );
    }

    /**
     * Returns a new path instance that has a portion of this path's atoms
     * replaced with a different sequence of atoms.
     *
     * @param integer       $offset
     * @param mixed<string> $replacement
     * @param integer|null  $length
     *
     * @return PathInterface
     */
    public function replace($offset, $replacement, $length = null)
    {
        $atoms = $this->atoms();

        if (!is_array($replacement)) {
            $replacement = iterator_to_array($replacement);
        }
        if (null === $length) {
            $length = count($atoms);
        }

        array_splice($atoms, $offset, $length, $replacement);

        return $this->createPath(
            $atoms,
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
        $name = $this->name();
        if (static::SELF_ATOM === $name) {
            return $this->replaceName($suffix);
        }

        return $this->replaceName($name . $suffix);
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
        $name = $this->name();
        if (static::SELF_ATOM === $name) {
            return $this->replaceName($prefix);
        }

        return $this->replaceName($prefix . $name);
    }

    /**
     * Returns a new path instance with the supplied name replacing the existing
     * one.
     *
     * @param string $name
     *
     * @return PathInterface
     */
    public function replaceName($name)
    {
        $atoms = $this->atoms();
        $numAtoms = count($atoms);

        if ($numAtoms > 0) {
            if ('' === $name) {
                array_pop($atoms);
            } else {
                $atoms[$numAtoms - 1] = $name;
            }
        } elseif ('' !== $name) {
            $atoms[] = $name;
        }

        return $this->createPath(
            $atoms,
            $this instanceof AbsolutePathInterface
        );
    }

    /**
     * Returns a new path instance with the supplied name replacing the portion
     * of the existing name preceding the last extension.
     *
     * @param string $nameWithoutExtension
     *
     * @return PathInterface
     */
    public function replaceNameWithoutExtension($nameWithoutExtension)
    {
        $atoms = $this->nameAtoms();
        if (count($atoms) < 2) {
            return $this->replaceName($nameWithoutExtension);
        }

        array_splice($atoms, 0, -1, array($nameWithoutExtension));

        return $this->replaceName(implode(self::EXTENSION_SEPARATOR, $atoms));
    }

    /**
     * Returns a new path instance with the supplied name prefix replacing the
     * existing one.
     *
     * @param string $namePrefix
     *
     * @return PathInterface
     */
    public function replaceNamePrefix($namePrefix)
    {
        $atoms = $this->nameAtoms();
        array_splice($atoms, 0, 1, array($namePrefix));

        return $this->replaceName(implode(self::EXTENSION_SEPARATOR, $atoms));
    }

    /**
     * Returns a new path instance with the supplied name suffix replacing the
     * existing one.
     *
     * @param string|null $nameSuffix
     *
     * @return PathInterface
     */
    public function replaceNameSuffix($nameSuffix)
    {
        $atoms = $this->nameAtoms();
        if (array('', '') === $atoms) {
            if (null === $nameSuffix) {
                return $this;
            }

            return $this->replaceName(
                static::EXTENSION_SEPARATOR . $nameSuffix
            );
        }

        $numAtoms = count($atoms);

        if (null === $nameSuffix) {
            $replacement = array();
        } else {
            $replacement = array($nameSuffix);
        }
        array_splice($atoms, 1, count($atoms), $replacement);

        return $this->replaceName(implode(self::EXTENSION_SEPARATOR, $atoms));
    }

    /**
     * Returns a new path instance with the supplied extension replacing the
     * existing one.
     *
     * @param string|null $extension
     *
     * @return PathInterface
     */
    public function replaceExtension($extension)
    {
        $atoms = $this->nameAtoms();
        if (array('', '') === $atoms) {
            if (null === $extension) {
                return $this;
            }

            return $this->replaceName(
                static::EXTENSION_SEPARATOR . $extension
            );
        }

        $numAtoms = count($atoms);

        if ($numAtoms > 1) {
            if (null === $extension) {
                $replacement = array();
            } else {
                $replacement = array($extension);
            }

            array_splice($atoms, -1, $numAtoms, $replacement);
        } elseif (null !== $extension) {
            $atoms[] = $extension;
        }

        return $this->replaceName(implode(self::EXTENSION_SEPARATOR, $atoms));
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
