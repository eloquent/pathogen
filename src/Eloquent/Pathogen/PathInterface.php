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

interface PathInterface
{
    /**
     * Returns the atoms of this path as an array of strings.
     *
     * For example, the path '/foo/bar' has the atoms 'foo' and 'bar'.
     *
     * @return array<integer,string>
     */
    public function atoms();

    /**
     * Returns a subsequence of the atoms of this path as an array of strings.
     *
     * @param integer      $index
     * @param integer|null $length
     *
     * @return array<integer,string>
     */
    public function sliceAtoms($index, $length = null);

    /**
     * Returns true is at least one atom is present.
     *
     * @return boolean
     */
    public function hasAtoms();

    /**
     * Returns true if this path ends with a path separator.
     *
     * @return boolean
     */
    public function hasTrailingSeparator();

    /**
     * Returns a string representation of this path.
     *
     * @return string
     */
    public function string();

    /**
     * Returns a string representation of this path.
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns the last atom of this path.
     *
     * If this path has no atoms, an empty string is returned.
     *
     * @return string
     */
    public function name();

    /**
     * Returns the atoms of this path's name as an array of strings.
     *
     * For example, the path name 'foo.bar' has the atoms 'foo' and 'bar'.
     *
     * @return array<integer,string>
     */
    public function nameAtoms();

    /**
     * Returns the last atom of this path, excluding the last extension.
     *
     * If this path has no atoms, an empty string is returned.
     *
     * @return string
     */
    public function nameWithoutExtension();

    /**
     * Returns the last atom of this path, excluding any extensions.
     *
     * If this path has no atoms, an empty string is returned.
     *
     * @return string
     */
    public function namePrefix();

    /**
     * Returns the extensions of this path's last atom.
     *
     * If the last atom has no extensions, or this path has no atoms, this
     * method will return null.
     *
     * @return string|null
     */
    public function nameSuffix();

    /**
     * Returns the last extension of this path's last atom.
     *
     * If the last atom has no extensions, or this path has no atoms, this
     * method will return null.
     *
     * @return string|null
     */
    public function extension();

    /**
     * Returns true if this path's last atom has any extensions.
     *
     * @return boolean
     */
    public function hasExtension();

    /**
     * Returns the parent of this path.
     *
     * @return PathInterface
     */
    public function parent();

    /**
     * Returns a new path instance with the trailing slash removed from this
     * path.
     *
     * If this path has no trailing slash, the path is returned unmodified.
     *
     * @return PathInterface
     */
    public function stripTrailingSlash();

    /**
     * Returns a new path instance with the last extension removed from this
     * path.
     *
     * If this path has no extensions, the path is returned unmodified.
     *
     * @return PathInterface
     */
    public function stripExtension();

    /**
     * Returns a new path instance with all extensions removed from this path.
     *
     * If this path has no extensions, the path is returned unmodified.
     *
     * @return PathInterface
     */
    public function stripNameSuffix();

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
    public function joinAtoms($atom);

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
    public function joinAtomSequence($atoms);

    /**
     * Returns a new path with the supplied path suffixed to this path.
     *
     * @param RelativePathInterface $path
     *
     * @return PathInterface
     */
    public function join(RelativePathInterface $path);

    /**
     * Returns a new path instance with a trailing slash suffixed to this path.
     *
     * @return PathInterface
     */
    public function joinTrailingSlash();

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
    public function joinExtensions($extension);

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
    public function joinExtensionSequence($extensions);

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
    public function suffixName($suffix);

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
    public function prefixName($prefix);

    /**
     * Returns a new path instance that has a portion of this path's atoms
     * replaced with a different sequence of atoms.
     *
     * @param integer       $index
     * @param mixed<string> $replacement
     * @param integer|null  $length
     *
     * @return PathInterface
     */
    public function replace($index, $replacement, $length = null);

    /**
     * Returns a new path instance with the supplied name replacing the existing
     * one.
     *
     * @param string $name
     *
     * @return PathInterface
     */
    public function replaceName($name);

    /**
     * Returns a new path instance with the supplied name replacing the portion
     * of the existing name preceding the last extension.
     *
     * @param string $nameWithoutExtension
     *
     * @return PathInterface
     */
    public function replaceNameWithoutExtension($nameWithoutExtension);

    /**
     * Returns a new path instance with the supplied name prefix replacing the
     * existing one.
     *
     * @param string $namePrefix
     *
     * @return PathInterface
     */
    public function replaceNamePrefix($namePrefix);

    /**
     * Returns a new path instance with the supplied name suffix replacing the
     * existing one.
     *
     * @param string|null $nameSuffix
     *
     * @return PathInterface
     */
    public function replaceNameSuffix($nameSuffix);

    /**
     * Returns a new path instance with the supplied extension replacing the
     * existing one.
     *
     * @param string|null $extension
     *
     * @return PathInterface
     */
    public function replaceExtension($extension);

    /**
     * Returns a new path instance that has a portion of this name's atoms
     * replaced with a different sequence of atoms.
     *
     * @param integer       $index
     * @param mixed<string> $replacement
     * @param integer|null  $length
     *
     * @return PathInterface
     */
    public function replaceNameAtoms($index, $replacement, $length = null);

    const ATOM_SEPARATOR = '/';
    const EXTENSION_SEPARATOR = '.';
    const PARENT_ATOM = '..';
    const SELF_ATOM = '.';
}
