<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Unix\Normalizer;

use Eloquent\Pathogen\Factory\PathFactoryInterface;
use Eloquent\Pathogen\Normalizer\PathNormalizer;
use Eloquent\Pathogen\Normalizer\PathNormalizerInterface;
use Eloquent\Pathogen\Unix\Factory\UnixPathFactory;

/**
 * A path normalizer suitable for normalizing Unix paths.
 */
class UnixPathNormalizer extends PathNormalizer
{
    /**
     * Get a static instance of this path normalizer.
     *
     * @return PathNormalizerInterface The static path normalizer.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Construct a new path normalizer.
     *
     * @param PathFactoryInterface|null $factory The path factory to use.
     */
    public function __construct(PathFactoryInterface $factory = null)
    {
        if (null === $factory) {
            $factory = UnixPathFactory::instance();
        }

        parent::__construct($factory);
    }

    private static $instance;
}
