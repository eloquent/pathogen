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
use Eloquent\Pathogen\Factory\PathFactoryInterface;
use Eloquent\Pathogen\FileSystem\Factory\PlatformFileSystemPathFactory;
use Eloquent\Pathogen\Resolver\BoundPathResolver;
use Eloquent\Pathogen\Resolver\PathResolverInterface;
use Icecave\Isolator\Isolator;

class WorkingDirectoryResolver extends BoundPathResolver
{
    /**
     * @param AbsolutePathInterface|null $workingDirectoryPath
     * @param PathResolverInterface|null $resolver
     * @param PathFactoryInterface|null  $factory
     * @param Isolator|null              $isolator
     */
    public function __construct(
        AbsolutePathInterface $workingDirectoryPath = null,
        PathResolverInterface $resolver = null,
        PathFactoryInterface $factory = null,
        Isolator $isolator = null
    ) {
        if (null === $factory) {
            $factory = new PlatformFileSystemPathFactory;
        }

        $this->factory = $factory;
        $this->isolator = Isolator::get($isolator);

        if (null === $workingDirectoryPath) {
            $workingDirectoryPath = $this->currentWorkingDirectoryPath();
        }

        parent::__construct($workingDirectoryPath, $resolver);
    }

    /**
     * @return PathFactoryInterface
     */
    public function factory()
    {
        return $this->factory;
    }

    /**
     * @return AbsolutePathInterface
     */
    protected function currentWorkingDirectoryPath()
    {
        return $this->factory()->create($this->isolator->getcwd());
    }

    private $factory;
    private $isolator;
}
