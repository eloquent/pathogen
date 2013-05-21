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

use Eloquent\Pathogen\Factory\PathFactory;
use Eloquent\Pathogen\Factory\PathFactoryInterface;
use Eloquent\Pathogen\Windows\Factory\WindowsPathFactory;

abstract class AbstractFileSystemPathFactory implements PathFactoryInterface
{
    /**
     * @param PathFactoryInterface|null $posixFactory
     * @param PathFactoryInterface|null $windowsFactory
     */
    public function __construct(
        PathFactoryInterface $posixFactory = null,
        PathFactoryInterface $windowsFactory = null
    ) {
        if (null === $posixFactory) {
            $posixFactory = new PathFactory;
        }
        if (null === $windowsFactory) {
            $windowsFactory = new WindowsPathFactory;
        }

        $this->posixFactory = $posixFactory;
        $this->windowsFactory = $windowsFactory;
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

    private $posixFactory;
    private $windowsFactory;
}
