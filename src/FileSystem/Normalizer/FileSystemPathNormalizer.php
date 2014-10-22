<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem\Normalizer;

use Eloquent\Pathogen\Normalizer\PathNormalizerInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\Unix\Normalizer\UnixPathNormalizer;
use Eloquent\Pathogen\Windows\Normalizer\WindowsPathNormalizer;
use Eloquent\Pathogen\Windows\WindowsPathInterface;

/**
 * A path normalizer capable or normalizing any type of file system path.
 */
class FileSystemPathNormalizer implements PathNormalizerInterface
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
     * Construct a new file system path normalizer.
     *
     * @param PathNormalizerInterface|null $unixNormalizer    The path normalizer to use for Unix paths.
     * @param PathNormalizerInterface|null $windowsNormalizer The path normalizer to use for Windows paths.
     */
    public function __construct(
        PathNormalizerInterface $unixNormalizer = null,
        PathNormalizerInterface $windowsNormalizer = null
    ) {
        if (null === $unixNormalizer) {
            $unixNormalizer = UnixPathNormalizer::instance();
        }
        if (null === $windowsNormalizer) {
            $windowsNormalizer = WindowsPathNormalizer::instance();
        }

        $this->unixNormalizer = $unixNormalizer;
        $this->windowsNormalizer = $windowsNormalizer;
    }

    /**
     * Get the path normalizer used for Unix paths.
     *
     * @return PathNormalizerInterface The path normalizer used for Unix paths.
     */
    public function unixNormalizer()
    {
        return $this->unixNormalizer;
    }

    /**
     * Get the path normalizer used for Windows paths.
     *
     * @return PathNormalizerInterface The path normalizer used for Windows paths.
     */
    public function windowsNormalizer()
    {
        return $this->windowsNormalizer;
    }

    /**
     * Normalize the supplied path to its most canonical form.
     *
     * @param PathInterface $path The path to normalize.
     *
     * @return PathInterface The normalized path.
     */
    public function normalize(PathInterface $path)
    {
        if ($path instanceof WindowsPathInterface) {
            return $this->windowsNormalizer()->normalize($path);
        }

        return $this->unixNormalizer()->normalize($path);
    }

    private static $instance;
    private $unixNormalizer;
    private $windowsNormalizer;
}
