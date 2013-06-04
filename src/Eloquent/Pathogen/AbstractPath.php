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
 * Abstract base class for implementing PathInterface.
 */
abstract class AbstractPath implements PathInterface
{
    /**
     * Construct a new path instance.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param boolean|null  $hasTrailingSeparator True if this path has a
     *     trailing separator.
     *
     * @throws Exception\InvalidPathAtomExceptionInterface If any of the
     *     supplied path atoms are invalid.
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
     * Get the atoms of this path.
     *
     * For example, the path '/foo/bar' has the atoms 'foo' and 'bar'.
     *
     * @return array<integer,string> The atoms of this path as an array of
     * strings.
     */
    public function atoms()
    {
        return $this->atoms;
    }

    /**
     * Get a subset of the atoms of this path.
     *
     * @param integer      $index  The index of the first atom.
     * @param integer|null $length The maximum number of atoms.
     *
     * @return array<integer,string> An array of strings representing the subset
     *     of path atoms.
     */
    public function sliceAtoms($index, $length = null)
    {
        $atoms = $this->atoms();
        if (null === $length) {
            $length = count($atoms);
        }

        return array_slice($atoms, $index, $length);
    }

    /**
     * Determine if this path has any atoms.
     *
     * @return boolean True if this path has at least one atom.
     */
    public function hasAtoms()
    {
        return count($this->atoms()) > 0;
    }

    /**
     * Determine if this path has a trailing separator.
     *
     * @return boolean True if this path has a trailing separator.
     */
    public function hasTrailingSeparator()
    {
        return $this->hasTrailingSeparator;
    }

    /**
     * Generate a string representation of this path.
     *
     * @return string A string representation of this path.
     */
    public function string()
    {
        return
            implode(static::ATOM_SEPARATOR, $this->atoms()) .
            ($this->hasTrailingSeparator() ? static::ATOM_SEPARATOR : '')
        ;
    }

    /**
     * Generate a string representation of this path.
     *
     * @return string A string representation of this path.
     */
    public function __toString()
    {
        return $this->string();
    }

    /**
     * Get this path's name.
     *
     * @return string The last path atom if one exists, otherwise an empty
     *     string.
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
     * Get this path's name atoms.
     *
     * For example, the path name 'foo.bar' has the atoms 'foo' and 'bar'.
     *
     * @return array<integer,string> The atoms of this path's name as an array
     *     of strings.
     */
    public function nameAtoms()
    {
        return explode(static::EXTENSION_SEPARATOR, $this->name());
    }

    /**
     * Get a subset of this path's name atoms.
     *
     * @param integer      $index  The index of the first atom.
     * @param integer|null $length The maximum number of atoms.
     *
     * @return array<integer,string> An array of strings representing the subset
     *     of path name atoms.
     */
    public function sliceNameAtoms($index, $length = null)
    {
        $atoms = $this->nameAtoms();
        if (null === $length) {
            $length = count($atoms);
        }

        return array_slice($atoms, $index, $length);
    }

    /**
     * Get this path's name, excluding the last extension.
     *
     * @return string The last atom of this path, excluding the last extension.
     *     If this path has no atoms, an empty string is returned.
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
     * Get this path's name, excluding all extensions.
     *
     * @return string The last atom of this path, excluding any extensions. If
     *     this path has no atoms, an empty string is returned.
     */
    public function namePrefix()
    {
        $atoms = $this->nameAtoms();

        return $atoms[0];
    }

    /**
     * Get all of this path's extensions.
     *
     * @return string|null The extensions of this path's last atom. If the last
     *     atom has no extensions, or this path has no atoms, this method will
     *     return null.
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
     * Get this path's last extension.
     *
     * @return string|null The last extension of this path's last atom. If the
     *     last atom has no extensions, or this path has no atoms, this method
     *     will return null.
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
     * Determine if this path has any extensions.
     *
     * @return boolean True if this path's last atom has any extensions.
     */
    public function hasExtension()
    {
        return count($this->nameAtoms()) > 1;
    }

    /**
     * Get the parent of this path.
     *
     * @return PathInterface The parent of this path.
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
     * Strips the trailing slash from this path.
     *
     * @return PathInterface A new path instance with the trailing slash removed
     *     from this path. If this path has no trailing slash, the path is
     *     returned unmodified.
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
     * Strips the last extension from this path.
     *
     * @return PathInterface A new path instance with the last extension removed
     *     from this path. If this path has no extensions, the path is returned
     *     unmodified.
     */
    public function stripExtension()
    {
        return $this->replaceExtension(null);
    }

    /**
     * Strips all extensions from this path.
     *
     * @return PathInterface A new path instance with all extensions removed
     *     from this path. If this path has no extensions, the path is returned
     *     unmodified.
     */
    public function stripNameSuffix()
    {
        return $this->replaceNameSuffix(null);
    }

    /**
     * Joins one or more atoms to this path.
     *
     * @param string     $atom            A path atom to append.
     * @param string,... $additionalAtoms Additional path atoms to append.
     *
     * @return PathInterface A new path with the supplied atom(s) suffixed to
     *     this path.
     * @throws Exception\InvalidPathAtomExceptionInterface If any joined atoms
     *     are invalid.
     */
    public function joinAtoms($atom)
    {
        return $this->joinAtomSequence(func_get_args());
    }

    /**
     * Joins a sequence of atoms to this path.
     *
     * @param mixed<string> $atoms The path atoms to append.
     *
     * @return PathInterface A new path with the supplied sequence of atoms
     *     suffixed to this path.
     * @throws Exception\InvalidPathAtomExceptionInterface If any joined atoms
     *     are invalid.
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
     * Joins the supplied path to this path.
     *
     * @param RelativePathInterface $path The path whose atoms should be joined
     *     to this path.
     *
     * @return PathInterface A new path with the supplied path suffixed to this
     *     path.
     */
    public function join(RelativePathInterface $path)
    {
        return $this->joinAtomSequence($path->atoms());
    }

    /**
     * Returns a new path instance with a trailing slash suffixed to this path.
     *
     * @return PathInterface
     */
    public function joinTrailingSlash()
    {
        if ($this->hasTrailingSeparator()) {
            return $this;
        }

        return $this->createPath(
            $this->atoms(),
            $this instanceof AbsolutePathInterface,
            true
        );
    }

    /**
     * Joins one or more extensions to this path.
     *
     * @param string     $extension            An extension to append.
     * @param string,... $additionalExtensions Additional extensions to append.
     *
     * @return PathInterface A new path instance with the supplied extensions
     *     suffixed to this path.
     * @throws Exception\InvalidPathAtomExceptionInterface If the suffixed
     *     extensions cause the atom to be invalid.
     */
    public function joinExtensions($extension)
    {
        return $this->joinExtensionSequence(func_get_args());
    }

    /**
     * Joins a sequence of extensions to this path.
     *
     * @param mixed<string> $extensions
     *
     * @return PathInterface A new path instance with the supplied extensions
     *     suffixed to this path.
     * @throws Exception\InvalidPathAtomExceptionInterface If the suffixed
     *     extensions cause the atom to be invalid.
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
     * Suffixes this path's name with a supplied string.
     *
     * @param string $suffix The string to suffix to the path name.
     *
     * @return PathInterface A new path instance with the supplied string
     *     suffixed to the last path atom.
     * @throws Exception\InvalidPathAtomExceptionInterface If the suffix causes
     *     the atom to be invalid.
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
     * Prefixes this path's name with a supplied string.
     *
     * @param string $prefix The string to prefix to the path name.
     *
     * @return PathInterface A new path instance with the supplied string
     *     prefixed to the last path atom.
     * @throws Exception\InvalidPathAtomExceptionInterface If the prefix causes
     *     the atom to be invalid.
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
     * Replace a section of this path with the supplied atom sequence.
     *
     * @param integer       $index       The start index of the replacement.
     * @param mixed<string> $replacement The replacement atom sequence.
     * @param integer|null  $length      The number of atoms to replace. If
     *     $length is null, the entire remainder of the path will be replaced.
     *
     * @return PathInterface A new path instance that has a portion of this
     *     path's atoms replaced with a different sequence of atoms.
     */
    public function replace($index, $replacement, $length = null)
    {
        $atoms = $this->atoms();

        if (!is_array($replacement)) {
            $replacement = iterator_to_array($replacement);
        }
        if (null === $length) {
            $length = count($atoms);
        }

        array_splice($atoms, $index, $length, $replacement);

        return $this->createPath(
            $atoms,
            $this instanceof AbsolutePathInterface
        );
    }

    /**
     * Replace this path's name.
     *
     * @param string $name The new path name.
     *
     * @return PathInterface A new path instance with the supplied name
     *     replacing the existing one.
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
     * Replace this path's name, but keep the last extension.
     *
     * @param string $nameWithoutExtension The replacement string.
     *
     * @return PathInterface A new path instance with the supplied name
     *     replacing the portion of the existing name preceding the last
     *     extension.
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
     * Replace this path's name, but keep any extensions.
     *
     * @param string $namePrefix The replacement string.
     *
     * @return PathInterface A new path instance with the supplied name prefix
     *     replacing the existing one.
     */
    public function replaceNamePrefix($namePrefix)
    {
        return $this->replaceNameAtoms(0, array($namePrefix), 1);
    }

    /**
     * Replace all of this path's extensions.
     *
     * @param string|null $nameSuffix The replacement string, or null to remove
     *     all extensions.
     *
     * @return PathInterface A new path instance with the supplied name suffix
     *     replacing the existing one.
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
     * Replace this path's last extension.
     *
     * @param string|null $extension The replacement string, or null to remove
     *     the last extension.
     *
     * @return PathInterface A new path instance with the supplied extension
     *     replacing the existing one.
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

    /**
     * Replace a section of this path's name with the supplied name atom
     * sequence.
     *
     * @param integer       $index       The start index of the replacement.
     * @param mixed<string> $replacement The replacement name atom sequence.
     * @param integer|null  $length      The number of atoms to replace. If
     *     $length is null, the entire remainder of the path name will be
     *     replaced.
     *
     * @return PathInterface A new path instance that has a portion of this
     *     name's atoms replaced with a different sequence of atoms.
     */
    public function replaceNameAtoms($index, $replacement, $length = null)
    {
        $atoms = $this->nameAtoms();

        if (!is_array($replacement)) {
            $replacement = iterator_to_array($replacement);
        }
        if (null === $length) {
            $length = count($atoms);
        }

        array_splice($atoms, $index, $length, $replacement);

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
