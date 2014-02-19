<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Factory\Consumer;

use Eloquent\Pathogen\Factory\PathFactory;
use Eloquent\Pathogen\Factory\PathFactoryInterface;

/**
 * A trait for classes that take a path factory as a dependency.
 */
trait PathFactoryTrait
{
    /**
     * Set the path factory.
     *
     * @param PathFactoryInterface $pathFactory The path factory to use.
     */
    public function setPathFactory(PathFactoryInterface $pathFactory)
    {
        $this->pathFactory = $pathFactory;
    }

    /**
     * Get the path factory.
     *
     * @return PathFactoryInterface The path factory.
     */
    public function pathFactory()
    {
        if (null === $this->pathFactory) {
            $this->pathFactory = $this->createDefaultPathFactory();
        }

        return $this->pathFactory;
    }

    /**
     * Create a new instance of the default path factory.
     *
     * @return PathFactoryInterface The new default path factory instance.
     */
    protected function createDefaultPathFactory()
    {
        return PathFactory::instance();
    }

    private $pathFactory;
}
