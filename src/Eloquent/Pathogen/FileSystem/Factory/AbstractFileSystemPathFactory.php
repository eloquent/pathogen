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

use Eloquent\Pathogen\AbsolutePathInterface;
use Eloquent\Pathogen\Factory\PathFactory;
use Eloquent\Pathogen\Factory\PathFactoryInterface;
use Eloquent\Pathogen\Windows\Factory\WindowsPathFactory;
use Icecave\Isolator\Isolator;

abstract class AbstractFileSystemPathFactory implements
    FileSystemPathFactoryInterface
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
        if (null === $posixFactory) {
            $posixFactory = new PathFactory;
        }
        if (null === $windowsFactory) {
            $windowsFactory = new WindowsPathFactory;
        }

        $this->posixFactory = $posixFactory;
        $this->windowsFactory = $windowsFactory;
        $this->isolator = Isolator::get($isolator);
    }

    /**
     * @return PathFactoryInterface
     */
    public function posixFactory()
    {
        return $this->posixFactory;
    }

    /**
     * @return PathFactoryInterface
     */
    public function windowsFactory()
    {
        return $this->windowsFactory;
    }

    /**
     * Returns a new path instance representing the current working directory
     * path.
     *
     * @return AbsolutePathInterface
     */
    public function createWorkingDirectoryPath()
    {
        return $this->factoryByPlatform()
            ->create($this->isolator()->getcwd());
    }

    /**
     * Returns a new path instance representing the system default temporary
     * directory path.
     *
     * @return AbsolutePathInterface
     */
    public function createTemporaryDirectoryPath()
    {
        return $this->factoryByPlatform()
            ->create($this->isolator()->sys_get_temp_dir());
    }

    /**
     * Returns a new path instance representing a path suitable for use as the
     * location for a new temporary file or directory.
     *
     * @param string|null $prefix A string to use as a prefix for the path name.
     *
     * @return AbsolutePathInterface
     */
    public function createTemporaryPath($prefix = null)
    {
        if (null === $prefix) {
            $prefix = '';
        }

        return $this->createTemporaryDirectoryPath()
            ->joinAtoms($this->isolator()->uniqid($prefix, true));
    }

    /**
     * @return Isolator
     */
    protected function isolator()
    {
        return $this->isolator;
    }

    /**
     * @return PathFactoryInterface
     */
    protected function factoryByPlatform()
    {
        if ($this->isolator()->defined('PHP_WINDOWS_VERSION_BUILD')) {
            return $this->windowsFactory();
        }

        return $this->posixFactory();
    }

    private $posixFactory;
    private $windowsFactory;
    private $isolator;
}
