<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem\Factory;

use Eloquent\Pathogen\Factory\PathFactoryInterface;
use Eloquent\Pathogen\PathInterface;
use Icecave\Isolator\Isolator;

class PlatformFileSystemPathFactory extends AbstractFileSystemPathFactory
{
    /**
     * @param PathFactoryInterface|null $posixFactory
     * @param PathFactoryInterface|null $windowsFactory
     * @param Isolator|null             $isolator
     */
    public function __construct(
        PathFactoryInterface $posixFactory = null,
        PathFactoryInterface $windowsFactory = null,
        Isolator $isolator = null
    ) {
        parent::__construct($posixFactory, $windowsFactory);

        $this->isolator = Isolator::get($isolator);
    }

    /**
     * Creates a new path instance from its string representation.
     *
     * @param string $path
     *
     * @return PathInterface
     */
    public function create($path)
    {
        return $this->factory()->create($path);
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
        return $this->factory()->createFromAtoms(
            $atoms,
            $isAbsolute,
            $hasTrailingSeparator
        );
    }

    /**
     * @return PathFactoryInterface
     */
    protected function factory()
    {
        if ($this->isolator->defined('PHP_WINDOWS_VERSION_BUILD')) {
            return $this->windowsFactory();
        }

        return $this->posixFactory();
    }

    private $isolator;
}
