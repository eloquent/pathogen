<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows;

use Eloquent\Pathogen\Exception\InvalidPathAtomCharacterException;
use Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException;
use Eloquent\Pathogen\FileSystem\AbstractRelativeFileSystemPath;

/**
 * Represents a relative Windows path.
 */
class RelativeWindowsPath extends AbstractRelativeFileSystemPath implements
    RelativeWindowsPathInterface
{
    /**
     * @param string $atom
     */
    protected function validateAtom($atom)
    {
        parent::validateAtom($atom);

        if (false !== strpos($atom, '\\')) {
            throw new PathAtomContainsSeparatorException($atom);
        } elseif (preg_match('/([\x00-\x1F<>:"|?*])/', $atom, $matches)) {
            throw new InvalidPathAtomCharacterException($atom, $matches[1]);
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
        return new static($atoms, $hasTrailingSeparator);
    }
}
