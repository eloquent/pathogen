<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows\Normalizer;

use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\Normalizer\PathNormalizer;
use Eloquent\Pathogen\Windows\AbsoluteWindowsPathInterface;
use Eloquent\Pathogen\Windows\Factory\WindowsPathFactory;
use Eloquent\Pathogen\Windows\Factory\WindowsPathFactoryInterface;

/**
 * A path normalizer suitable for normalizing Windows paths.
 */
class WindowsPathNormalizer extends PathNormalizer
{
    /**
     * Construct a new Windows path normalizer.
     *
     * @param WindowsPathFactoryInterface|null $factory The path factory to use.
     */
    public function __construct(WindowsPathFactoryInterface $factory = null)
    {
        if (null === $factory) {
            $factory = new WindowsPathFactory;
        }

        parent::__construct($factory);
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
        if ($path instanceof AbsoluteWindowsPathInterface) {
            return $this->normalizeAbsoluteWindowsPath($path);
        }

        return parent::normalize($path);
    }

    /**
     * Normalize an absolute Windows path.
     *
     * @param AbsoluteWindowsPathInterface $path The path to normalize.
     *
     * @return AbsoluteWindowsPathInterface The normalized path.
     */
    protected function normalizeAbsoluteWindowsPath(
        AbsoluteWindowsPathInterface $path
    ) {
        return $this->factory()->createFromDriveAndAtoms(
            $this->normalizeAbsolutePathAtoms($path->atoms()),
            $this->normalizeDriveSpecifier($path->drive()),
            true,
            false
        );
    }

    /**
     * Normalize a Windows path drive specifier.
     *
     * @param string|null $drive The drive specifier to normalize.
     *
     * @return string|null The normalized drive specifier.
     */
    protected function normalizeDriveSpecifier($drive)
    {
        if (null === $drive) {
            return null;
        }

        return strtoupper($drive);
    }
}
