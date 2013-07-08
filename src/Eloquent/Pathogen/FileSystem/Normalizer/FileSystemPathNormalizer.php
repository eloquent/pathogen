<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\FileSystem\Normalizer;

use Eloquent\Pathogen\Normalizer\PathNormalizer;
use Eloquent\Pathogen\Normalizer\PathNormalizerInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\Windows\Normalizer\WindowsPathNormalizer;
use Eloquent\Pathogen\Windows\WindowsPathInterface;

/**
 * A path normalizer capable or normalizing any type of file system path.
 */
class FileSystemPathNormalizer implements PathNormalizerInterface
{
    /**
     * Construct a new file system path normalizer.
     *
     * @param PathNormalizerInterface|null $posixNormalizer The path normalizer
     *     to use for Unix-style paths.
     * @param PathNormalizerInterface|null $windowsNormalizer The path
     *     normalizer to use for Windows paths.
     */
    public function __construct(
        PathNormalizerInterface $posixNormalizer = null,
        PathNormalizerInterface $windowsNormalizer = null
    ) {
        if (null === $posixNormalizer) {
            $posixNormalizer = new PathNormalizer;
        }
        if (null === $windowsNormalizer) {
            $windowsNormalizer = new WindowsPathNormalizer;
        }

        $this->posixNormalizer = $posixNormalizer;
        $this->windowsNormalizer = $windowsNormalizer;
    }

    /**
     * Get the path normalizer used for Unix-style paths.
     *
     * @return PathNormalizerInterface The path normalizer used for Unix-style
     *     paths.
     */
    public function posixNormalizer()
    {
        return $this->posixNormalizer;
    }

    /**
     * Get the path normalizer used for Windows paths.
     *
     * @return PathNormalizerInterface The path normalizer used for Windows
     *     paths.
     */
    public function windowsNormalizer()
    {
        return $this->windowsNormalizer;
    }

    /**
     * Normalize the supplied path to it's most canonical form.
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

        return $this->posixNormalizer()->normalize($path);
    }

    private $posixNormalizer;
    private $windowsNormalizer;
}
