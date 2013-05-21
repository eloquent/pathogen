<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem;

use Eloquent\Pathogen\PathInterface;

class FileSystemPathFactory extends AbstractFileSystemPathFactory
{
    /**
     * Creates a new path instance from its string representation.
     *
     * @param string $path
     *
     * @return PathInterface
     */
    public function create($path)
    {
        if (preg_match('/^([a-zA-Z]):/', $path)) {
            return $this->windowsFactory()->create($path);
        }

        return $this->posixFactory()->create($path);
    }

    /**
     * Creates a new path instance from a set of path atoms.
     *
     * Unless otherwise specified, created paths will be absolute, and have no
     * trailing separator.
     *
     * @param mixed<string> $atoms
     * @param boolean|null  $isAbsolute
     * @param boolean|null  $hasTrailingSeparator
     *
     * @return PathInterface
     * @throws InvalidPathAtomExceptionInterface If any supplied atom is
     * invalid.
     */
    public function createFromAtoms(
        $atoms,
        $isAbsolute = null,
        $hasTrailingSeparator = null
    ) {
        return $this->posixFactory()->createFromAtoms(
            $atoms,
            $isAbsolute,
            $hasTrailingSeparator
        );
    }
}
