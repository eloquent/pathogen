<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem\Resolver;

use Eloquent\Pathogen\AbsolutePathInterface;
use Eloquent\Pathogen\FileSystem\Factory\FileSystemPathFactoryInterface;
use Eloquent\Pathogen\FileSystem\Factory\PlatformFileSystemPathFactory;
use Eloquent\Pathogen\Resolver\BoundPathResolver;
use Eloquent\Pathogen\Resolver\PathResolverInterface;

/**
 * A bound path resolver for resolving file system paths against the current
 * working directory.
 */
class WorkingDirectoryResolver extends BoundPathResolver
{
    /**
     * Construct a new working directory path resolver.
     *
     * @param AbsolutePathInterface|null          $workingDirectoryPath The working directory path.
     * @param PathResolverInterface|null          $resolver             The path resolver to use.
     * @param FileSystemPathFactoryInterface|null $factory              The path factory to use.
     */
    public function __construct(
        AbsolutePathInterface $workingDirectoryPath = null,
        PathResolverInterface $resolver = null,
        FileSystemPathFactoryInterface $factory = null
    ) {
        if (null === $factory) {
            $factory = PlatformFileSystemPathFactory::instance();
        }

        $this->factory = $factory;

        if (null === $workingDirectoryPath) {
            $workingDirectoryPath = $this->factory()
                ->createWorkingDirectoryPath();
        }

        parent::__construct($workingDirectoryPath, $resolver);
    }

    /**
     * Get the path factory used by this resolver.
     *
     * @return FileSystemPathFactoryInterface The path factory.
     */
    public function factory()
    {
        return $this->factory;
    }

    private $factory;
}
