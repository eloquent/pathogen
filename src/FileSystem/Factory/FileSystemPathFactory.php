<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem\Factory;

use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use Eloquent\Pathogen\Exception\InvalidPathStateException;
use Eloquent\Pathogen\PathInterface;

/**
 * A path factory that produces file system paths by inspecting the supplied
 * string, and determining the most suitable path type to use.
 */
class FileSystemPathFactory extends AbstractFileSystemPathFactory
{
    /**
     * Get a static instance of this path factory.
     *
     * @return FileSystemPathFactoryInterface The static path factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Creates a new path instance from its string representation.
     *
     * @param string $path The string representation of the path.
     *
     * @return PathInterface The newly created path instance.
     */
    public function create($path)
    {
        if (preg_match('{^([a-zA-Z]):}', $path)) {
            return $this->windowsFactory()->create($path);
        }

        return $this->unixFactory()->create($path);
    }

    /**
     * Creates a new path instance from a set of path atoms.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param boolean|null  $isAbsolute           True if the path is absolute.
     * @param boolean|null  $hasTrailingSeparator True if the path has a trailing separator.
     *
     * @return PathInterface                     The newly created path instance.
     * @throws InvalidPathAtomExceptionInterface If any of the supplied atoms are invalid.
     * @throws InvalidPathStateException         If the supplied arguments would produce an invalid path.
     */
    public function createFromAtoms(
        $atoms,
        $isAbsolute = null,
        $hasTrailingSeparator = null
    ) {
        return $this->unixFactory()->createFromAtoms(
            $atoms,
            $isAbsolute,
            $hasTrailingSeparator
        );
    }

    private static $instance;
}
