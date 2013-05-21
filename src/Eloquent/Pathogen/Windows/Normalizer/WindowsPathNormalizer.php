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

class WindowsPathNormalizer extends PathNormalizer
{
    /**
     * @param WindowsPathFactoryInterface|null $factory
     */
    public function __construct(WindowsPathFactoryInterface $factory = null)
    {
        if (null === $factory) {
            $factory = new WindowsPathFactory;
        }

        parent::__construct($factory);
    }

    /**
     * @param PathInterface $path
     *
     * @return PathInterface
     */
    public function normalize(PathInterface $path)
    {
        if ($path instanceof AbsoluteWindowsPathInterface) {
            return $this->normalizeAbsoluteWindowsPath($path);
        }

        return parent::normalize($path);
    }

    /**
     * @param AbsoluteWindowsPathInterface $path
     *
     * @return AbsoluteWindowsPathInterface
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
     * @param string|null $drive
     *
     * @return string|null
     */
    protected function normalizeDriveSpecifier($drive)
    {
        if (null === $drive) {
            return null;
        }

        return strtoupper($drive);
    }
}
