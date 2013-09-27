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
use Eloquent\Pathogen\Unix\Factory\UnixPathFactory;

/**
 * A path normalizer capable or normalizing any type of file system path.
 */
class FileSystemPathNormalizer implements PathNormalizerInterface
{
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
            $unixNormalizer = new PathNormalizer(
                new UnixPathFactory
            );
        }
        if (null === $windowsNormalizer) {
            $windowsNormalizer = new WindowsPathNormalizer;
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

    private $unixNormalizer;
    private $windowsNormalizer;
}
