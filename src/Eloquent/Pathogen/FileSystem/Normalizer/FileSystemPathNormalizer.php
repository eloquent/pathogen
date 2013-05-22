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

class FileSystemPathNormalizer implements PathNormalizerInterface
{
    /**
     * @param PathNormalizerInterface|null $posixNormalizer
     * @param PathNormalizerInterface|null $windowsNormalizer
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
     * @return PathNormalizerInterface
     */
    public function posixNormalizer()
    {
        return $this->posixNormalizer;
    }

    /**
     * @return PathNormalizerInterface
     */
    public function windowsNormalizer()
    {
        return $this->windowsNormalizer;
    }

    /**
     * @param PathInterface $path
     *
     * @return PathInterface
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
