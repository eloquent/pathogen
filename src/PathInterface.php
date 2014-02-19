<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
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
     * @return array<integer,string> The atoms of this path as an array of strings.
     */
    public function atoms();

    /**
     * Get a single path atom by index.
     *
     * @param integer $index The index to search for.
     *
     * @return string                           The path atom.
     * @throws Exception\UndefinedAtomException If the index does not exist in this path's atoms.
     */
    public function atomAt($index);

    /**
     * Get a single path atom by index, falling back to a default if the index
     * is undefined.
     *
     * @param integer $index   The index to search for.
     * @param mixed   $default The default value to return if no atom is defined for the supplied index.
     *
     * @return mixed The path atom, or $default if no atom is defined for the supplied index.
     */
    public function atomAtDefault($index, $default = null);

    /**
     * Get a subset of the atoms of this path.
     *
     * @param integer      $index  The index of the first atom.
     * @param integer|null $length The maximum number of atoms.
     *
     * @return array<integer,string> An array of strings representing the subset of path atoms.
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
     * @return string The last path atom if one exists, otherwise an empty string.
     */
    public function name();

    /**
     * Get this path's name atoms.
     *
     * For example, the path name 'foo.bar' has the atoms 'foo' and 'bar'.
     *
     * @return array<integer,string> The atoms of this path's name as an array of strings.
     */
    public function nameAtoms();

    /**
     * Get a single path name atom by index.
     *
     * @param integer $index The index to search for.
     *
     * @return string                           The path name atom.
     * @throws Exception\UndefinedAtomException If the index does not exist in this path's name atoms.
     */
    public function nameAtomAt($index);

    /**
     * Get a single path name atom by index, falling back to a default if the
     * index is undefined.
     *
     * @param integer $index   The index to search for.
     * @param mixed   $default The default value to return if no atom is defined for the supplied index.
     *
     * @return mixed The path name atom, or $default if no atom is defined for the supplied index.
     */
    public function nameAtomAtDefault($index, $default = null);

    /**
     * Get a subset of this path's name atoms.
     *
     * @param integer      $index  The index of the first atom.
     * @param integer|null $length The maximum number of atoms.
     *
     * @return array<integer,string> An array of strings representing the subset of path name atoms.
     */
    public function sliceNameAtoms($index, $length = null);

    /**
     * Get this path's name, excluding the last extension.
     *
     * @return string The last atom of this path, excluding the last extension. If this path has no atoms, an empty string is returned.
     */
    public function nameWithoutExtension();

    /**
     * Get this path's name, excluding all extensions.
     *
     * @return string The last atom of this path, excluding any extensions. If this path has no atoms, an empty string is returned.
     */
    public function namePrefix();

    /**
     * Get all of this path's extensions.
     *
     * @return string|null The extensions of this path's last atom. If the last atom has no extensions, or this path has no atoms, this method will return null.
     */
    public function nameSuffix();

    /**
     * Get this path's last extension.
     *
     * @return string|null The last extension of this path's last atom. If the last atom has no extensions, or this path has no atoms, this method will return null.
     */
    public function extension();

    /**
     * Determine if this path has any extensions.
     *
     * @return boolean True if this path's last atom has any extensions.
     */
    public function hasExtension();

    /**
     * Determine if this path contains a substring.
     *
     * @param string       $needle        The substring to search for.
     * @param boolean|null $caseSensitive True if case sensitive.
     *
     * @return boolean True if this path contains the substring.
     */
    public function contains($needle, $caseSensitive = null);

    /**
     * Determine if this path starts with a substring.
     *
     * @param string       $needle        The substring to search for.
     * @param boolean|null $caseSensitive True if case sensitive.
     *
     * @return boolean True if this path starts with the substring.
     */
    public function startsWith($needle, $caseSensitive = null);

    /**
     * Determine if this path ends with a substring.
     *
     * @param string       $needle        The substring to search for.
     * @param boolean|null $caseSensitive True if case sensitive.
     *
     * @return boolean True if this path ends with the substring.
     */
    public function endsWith($needle, $caseSensitive = null);

    /**
     * Determine if this path matches a wildcard pattern.
     *
     * @param string       $pattern       The pattern to check against.
     * @param boolean|null $caseSensitive True if case sensitive.
     * @param integer|null $flags         Additional flags.
     *
     * @return boolean True if this path matches the pattern.
     */
    public function matches($pattern, $caseSensitive = null, $flags = null);

    /**
     * Determine if this path matches a regular expression.
     *
     * @param string       $pattern  The pattern to check against.
     * @param array|null   &$matches Populated with the pattern matches.
     * @param integer|null $flags    Additional flags.
     * @param integer|null $offset   Start searching from this byte offset.
     *
     * @return boolean True if this path matches the pattern.
     */
    public function matchesRegex(
        $pattern,
        array &$matches = null,
        $flags = null,
        $offset = null
    );

    /**
     * Determine if this path's name contains a substring.
     *
     * @param string       $needle        The substring to search for.
     * @param boolean|null $caseSensitive True if case sensitive.
     *
     * @return boolean True if this path's name contains the substring.
     */
    public function nameContains($needle, $caseSensitive = null);

    /**
     * Determine if this path's name starts with a substring.
     *
     * @param string       $needle        The substring to search for.
     * @param boolean|null $caseSensitive True if case sensitive.
     *
     * @return boolean True if this path's name starts with the substring.
     */
    public function nameStartsWith($needle, $caseSensitive = null);

    /**
     * Determine if this path's name matches a wildcard pattern.
     *
     * @param string       $pattern       The pattern to check against.
     * @param boolean|null $caseSensitive True if case sensitive.
     * @param integer|null $flags         Additional flags.
     *
     * @return boolean True if this path's name matches the pattern.
     */
    public function nameMatches($pattern, $caseSensitive = null, $flags = null);

    /**
     * Determine if this path's name matches a regular expression.
     *
     * @param string       $pattern  The pattern to check against.
     * @param array|null   &$matches Populated with the pattern matches.
     * @param integer|null $flags    Additional flags.
     * @param integer|null $offset   Start searching from this byte offset.
     *
     * @return boolean True if this path's name matches the pattern.
     */
    public function nameMatchesRegex(
        $pattern,
        array &$matches = null,
        $flags = null,
        $offset = null
    );

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
     * @return PathInterface A new path instance with the trailing slash removed from this path. If this path has no trailing slash, the path is returned unmodified.
     */
    public function stripTrailingSlash();

    /**
     * Strips the last extension from this path.
     *
     * @return PathInterface A new path instance with the last extension removed from this path. If this path has no extensions, the path is returned unmodified.
     */
    public function stripExtension();

    /**
     * Strips all extensions from this path.
     *
     * @return PathInterface A new path instance with all extensions removed from this path. If this path has no extensions, the path is returned unmodified.
     */
    public function stripNameSuffix();

    /**
     * Joins one or more atoms to this path.
     *
     * @param string     $atom            A path atom to append.
     * @param string,... $additionalAtoms Additional path atoms to append.
     *
     * @return PathInterface                               A new path with the supplied atom(s) suffixed to this path.
     * @throws Exception\InvalidPathAtomExceptionInterface If any joined atoms are invalid.
     */
    public function joinAtoms($atom);

    /**
     * Joins a sequence of atoms to this path.
     *
     * @param mixed<string> $atoms The path atoms to append.
     *
     * @return PathInterface                               A new path with the supplied sequence of atoms suffixed to this path.
     * @throws Exception\InvalidPathAtomExceptionInterface If any joined atoms are invalid.
     */
    public function joinAtomSequence($atoms);

    /**
     * Joins the supplied path to this path.
     *
     * @param RelativePathInterface $path The path whose atoms should be joined to this path.
     *
     * @return PathInterface A new path with the supplied path suffixed to this path.
     */
    public function join(RelativePathInterface $path);

    /**
     * Adds a trailing slash to this path.
     *
     * @return PathInterface A new path instance with a trailing slash suffixed to this path.
     */
    public function joinTrailingSlash();

    /**
     * Joins one or more extensions to this path.
     *
     * @param string     $extension            An extension to append.
     * @param string,... $additionalExtensions Additional extensions to append.
     *
     * @return PathInterface                               A new path instance with the supplied extensions suffixed to this path.
     * @throws Exception\InvalidPathAtomExceptionInterface If the suffixed extensions cause the atom to be invalid.
     */
    public function joinExtensions($extension);

    /**
     * Joins a sequence of extensions to this path.
     *
     * @param mixed<string> $extensions The extensions to append.
     *
     * @return PathInterface                               A new path instance with the supplied extensions suffixed to this path.
     * @throws Exception\InvalidPathAtomExceptionInterface If the suffixed extensions cause the atom to be invalid.
     */
    public function joinExtensionSequence($extensions);

    /**
     * Suffixes this path's name with a supplied string.
     *
     * @param string $suffix The string to suffix to the path name.
     *
     * @return PathInterface                               A new path instance with the supplied string suffixed to the last path atom.
     * @throws Exception\InvalidPathAtomExceptionInterface If the suffix causes the atom to be invalid.
     */
    public function suffixName($suffix);

    /**
     * Prefixes this path's name with a supplied string.
     *
     * @param string $prefix The string to prefix to the path name.
     *
     * @return PathInterface                               A new path instance with the supplied string prefixed to the last path atom.
     * @throws Exception\InvalidPathAtomExceptionInterface If the prefix causes the atom to be invalid.
     */
    public function prefixName($prefix);

    /**
     * Replace a section of this path with the supplied atom sequence.
     *
     * @param integer       $index       The start index of the replacement.
     * @param mixed<string> $replacement The replacement atom sequence.
     * @param integer|null  $length      The number of atoms to replace. If $length is null, the entire remainder of the path will be replaced.
     *
     * @return PathInterface A new path instance that has a portion of this path's atoms replaced with a different sequence of atoms.
     */
    public function replace($index, $replacement, $length = null);

    /**
     * Replace this path's name.
     *
     * @param string $name The new path name.
     *
     * @return PathInterface A new path instance with the supplied name replacing the existing one.
     */
    public function replaceName($name);

    /**
     * Replace this path's name, but keep the last extension.
     *
     * @param string $nameWithoutExtension The replacement string.
     *
     * @return PathInterface A new path instance with the supplied name replacing the portion of the existing name preceding the last extension.
     */
    public function replaceNameWithoutExtension($nameWithoutExtension);

    /**
     * Replace this path's name, but keep any extensions.
     *
     * @param string $namePrefix The replacement string.
     *
     * @return PathInterface A new path instance with the supplied name prefix replacing the existing one.
     */
    public function replaceNamePrefix($namePrefix);

    /**
     * Replace all of this path's extensions.
     *
     * @param string|null $nameSuffix The replacement string, or null to remove all extensions.
     *
     * @return PathInterface A new path instance with the supplied name suffix replacing the existing one.
     */
    public function replaceNameSuffix($nameSuffix);

    /**
     * Replace this path's last extension.
     *
     * @param string|null $extension The replacement string, or null to remove the last extension.
     *
     * @return PathInterface A new path instance with the supplied extension replacing the existing one.
     */
    public function replaceExtension($extension);

    /**
     * Replace a section of this path's name with the supplied name atom
     * sequence.
     *
     * @param integer       $index       The start index of the replacement.
     * @param mixed<string> $replacement The replacement name atom sequence.
     * @param integer|null  $length      The number of atoms to replace. If $length is null, the entire remainder of the path name will be replaced.
     *
     * @return PathInterface A new path instance that has a portion of this name's atoms replaced with a different sequence of atoms.
     */
    public function replaceNameAtoms($index, $replacement, $length = null);

    /**
     * Get an absolute version of this path.
     *
     * If this path is relative, a new absolute path with equivalent atoms will
     * be returned. Otherwise, this path will be retured unaltered.
     *
     * @return AbsolutePathInterface               An absolute version of this path.
     * @throws Exception\InvalidPathStateException If absolute conversion is not possible for this path.
     */
    public function toAbsolute();

    /**
     * Get a relative version of this path.
     *
     * If this path is absolute, a new relative path with equivalent atoms will
     * be returned. Otherwise, this path will be retured unaltered.
     *
     * @return RelativePathInterface        A relative version of this path.
     * @throws Exception\EmptyPathException If this path has no atoms.
     */
    public function toRelative();

    /**
     * Normalize this path to its most canonical form.
     *
     * @return PathInterface The normalized path.
     */
    public function normalize();
}
