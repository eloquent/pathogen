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

class WorkingDirectoryResolver extends BoundPathResolver
{
    /**
     * @param AbsolutePathInterface|null          $workingDirectoryPath
     * @param PathResolverInterface|null          $resolver
     * @param FileSystemPathFactoryInterface|null $factory
     */
    public function __construct(
        AbsolutePathInterface $workingDirectoryPath = null,
        PathResolverInterface $resolver = null,
        FileSystemPathFactoryInterface $factory = null
    ) {
        if (null === $factory) {
            $factory = new PlatformFileSystemPathFactory;
        }

        $this->factory = $factory;

        if (null === $workingDirectoryPath) {
            $workingDirectoryPath = $this->factory()
                ->createWorkingDirectoryPath();
        }

        parent::__construct($workingDirectoryPath, $resolver);
    }

    /**
     * @return FileSystemPathFactoryInterface
     */
    public function factory()
    {
        return $this->factory;
    }

    private $factory;
}
