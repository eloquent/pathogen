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
 * The interface implemented by all Pathogen paths.
 */
interface PathInterface
{
    /**
     * Get the atoms of this path.
     *
     * For example, the path '/foo/bar' has the atoms 'foo' and 'bar'.
     *
     * @return array<integer,string> The atoms of this path as an array of
     * strings.
     */
    public function atoms();

    /**
     * Get a subset of the atoms of this path.
     *
     * @param integer      $index  The index of the first atom.
     * @param integer|null $length The maximum number of atoms.
     *
     * @return array<integer,string> An array of strings representing the subset
     *     of path atoms.
     */
    public function sliceAtoms($index, $length = null);

    /**
     * Determine if this path has any atoms.
     *
     * @return boolean True if this path has at least one atom.
     */
    public function hasAtoms();

    /**
     * Determine if this path has a trailing separator.
     *
     * @return boolean True if this path has a trailing separator.
     */
    public function hasTrailingSeparator();

    /**
     * Generate a string representation of this path.
     *
     * @return string A string representation of this path.
     */
    public function string();

    /**
     * Generate a string representation of this path.
     *
     * @return string A string representation of this path.
     */
    public function __toString();

    /**
     * Get this path's name.
     *
     * @return string The last path atom if one exists, otherwise an empty
     *     string.
     */
    public function name();

    /**
     * Get this path's name atoms.
     *
     * For example, the path name 'foo.bar' has the atoms 'foo' and 'bar'.
     *
     * @return array<integer,string> The atoms of this path's name as an array
     *     of strings.
     */
    public function nameAtoms();

    /**
     * Get a subset of this path's name atoms.
     *
     * @param integer      $index  The index of the first atom.
     * @param integer|null $length The maximum number of atoms.
     *
     * @return array<integer,string> An array of strings representing the subset
     *     of path name atoms.
     */
    public function sliceNameAtoms($index, $length = null);

    /**
     * Get this path's name, excluding the last extension.
     *
     * @return string The last atom of this path, excluding the last extension.
     *     If this path has no atoms, an empty string is returned.
     */
    public function nameWithoutExtension();

    /**
     * Get this path's name, excluding all extensions.
     *
     * @return string The last atom of this path, excluding any extensions. If
     *     this path has no atoms, an empty string is returned.
     */
    public function namePrefix();

    /**
     * Get all of this path's extensions.
     *
     * @return string|null The extensions of this path's last atom. If the last
     *     atom has no extensions, or this path has no atoms, this method will
     *     return null.
     */
    public function nameSuffix();

    /**
     * Get this path's last extension.
     *
     * @return string|null The last extension of this path's last atom. If the
     *     last atom has no extensions, or this path has no atoms, this method
     *     will return null.
     */
    public function extension();

    /**
     * Determine if this path has any extensions.
     *
     * @return boolean True if this path's last atom has any extensions.
     */
    public function hasExtension();

    /**
     * Get the parent of this path a specified number of levels up.
     *
     * @param integer|null $numLevels The number of levels up. Defaults to 1.
     *
     * @return PathInterface The parent of this path $numLevels up.
     */
    public function parent($numLevels = null);

    /**
     * Strips the trailing slash from this path.
     *
     * @return PathInterface A new path instance with the trailing slash removed
     *     from this path. If this path has no trailing slash, the path is
     *     returned unmodified.
     */
    public function stripTrailingSlash();

    /**
     * Strips the last extension from this path.
     *
     * @return PathInterface A new path instance with the last extension removed
     *     from this path. If this path has no extensions, the path is returned
     *     unmodified.
     */
    public function stripExtension();

    /**
     * Strips all extensions from this path.
     *
     * @return PathInterface A new path instance with all extensions removed
     *     from this path. If this path has no extensions, the path is returned
     *     unmodified.
     */
    public function stripNameSuffix();

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
    public function joinAtoms($atom);

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
    public function joinAtomSequence($atoms);

    /**
     * Joins the supplied path to this path.
     *
     * @param RelativePathInterface $path The path whose atoms should be joined
     *     to this path.
     *
     * @return PathInterface A new path with the supplied path suffixed to this
     *     path.
     */
    public function join(RelativePathInterface $path);

    /**
     * Adds a trailing slash to this path.
     *
     * @return PathInterface A new path instance with a trailing slash suffixed
     *     to this path.
     */
    public function joinTrailingSlash();

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
    public function joinExtensions($extension);

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
    public function joinExtensionSequence($extensions);

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
    public function suffixName($suffix);

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
    public function prefixName($prefix);

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
    public function replace($index, $replacement, $length = null);

    /**
     * Replace this path's name.
     *
     * @param string $name The new path name.
     *
     * @return PathInterface A new path instance with the supplied name
     *     replacing the existing one.
     */
    public function replaceName($name);

    /**
     * Replace this path's name, but keep the last extension.
     *
     * @param string $nameWithoutExtension The replacement string.
     *
     * @return PathInterface A new path instance with the supplied name
     *     replacing the portion of the existing name preceding the last
     *     extension.
     */
    public function replaceNameWithoutExtension($nameWithoutExtension);

    /**
     * Replace this path's name, but keep any extensions.
     *
     * @param string $namePrefix The replacement string.
     *
     * @return PathInterface A new path instance with the supplied name prefix
     *     replacing the existing one.
     */
    public function replaceNamePrefix($namePrefix);

    /**
     * Replace all of this path's extensions.
     *
     * @param string|null $nameSuffix The replacement string, or null to remove
     *     all extensions.
     *
     * @return PathInterface A new path instance with the supplied name suffix
     *     replacing the existing one.
     */
    public function replaceNameSuffix($nameSuffix);

    /**
     * Replace this path's last extension.
     *
     * @param string|null $extension The replacement string, or null to remove
     *     the last extension.
     *
     * @return PathInterface A new path instance with the supplied extension
     *     replacing the existing one.
     */
    public function replaceExtension($extension);

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
    public function replaceNameAtoms($index, $replacement, $length = null);

    /**
     * The character used to separate path atoms.
     */
    const ATOM_SEPARATOR = '/';

    /**
     * The character used to separate path name atoms.
     */
    const EXTENSION_SEPARATOR = '.';

    /**
     * The atom used to represent 'parent'.
     */
    const PARENT_ATOM = '..';

    /**
     * The atom used to represent 'self'.
     */
    const SELF_ATOM = '.';
}
