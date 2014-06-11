<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows\Normalizer;

use Eloquent\Pathogen\Normalizer\PathNormalizer;
use Eloquent\Pathogen\Normalizer\PathNormalizerInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\Windows\AbsoluteWindowsPathInterface;
use Eloquent\Pathogen\Windows\Factory\WindowsPathFactory;
use Eloquent\Pathogen\Windows\Factory\WindowsPathFactoryInterface;
use Eloquent\Pathogen\Windows\RelativeWindowsPathInterface;

/**
 * A path normalizer suitable for normalizing Windows paths.
 */
class WindowsPathNormalizer extends PathNormalizer
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
     * Construct a new Windows path normalizer.
     *
     * @param WindowsPathFactoryInterface|null $factory The path factory to use.
     */
    public function __construct(WindowsPathFactoryInterface $factory = null)
    {
        if (null === $factory) {
            $factory = WindowsPathFactory::instance();
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

        return $this->normalizeRelativeWindowsPath($path);
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
            false,
            false
        );
    }

    /**
     * Normalize a relative Windows path.
     *
     * @param RelativeWindowsPathInterface $path The path to normalize.
     *
     * @return RelativeWindowsPathInterface The normalized path.
     */
    protected function normalizeRelativeWindowsPath(
        RelativeWindowsPathInterface $path
    ) {
        if ($path->isAnchored()) {
            $atoms = $this->normalizeAbsolutePathAtoms($path->atoms());
        } else {
            $atoms = $this->normalizeRelativePathAtoms($path->atoms());
        }

        return $this->factory()->createFromDriveAndAtoms(
            $atoms,
            $this->normalizeDriveSpecifier($path->drive()),
            false,
            $path->isAnchored(),
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

    private static $instance;
}
