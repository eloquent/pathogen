<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Exception;

use Eloquent\Pathogen\PathInterface;
use Exception;

/**
 * Abstract base class for exceptions implementing
 * InvalidPathExceptionInterface.
 */
abstract class AbstractInvalidPathException extends Exception
    implements InvalidPathExceptionInterface
{
    /**
     * Construct a new invalid path exception.
     *
     * @param PathInterface  $path     The invalid path.
     * @param Exception|null $previous The cause, if available.
     */
    public function __construct(PathInterface $path, Exception $previous = null)
    {
        $this->path = $path;

        parent::__construct(
            sprintf(
                "Invalid path %s. %s",
                var_export($path->string(), true),
                $this->reason()
            ),
            0,
            $previous
        );
    }

    /**
     * Get the invalid path.
     *
     * @return PathInterface The invalid path.
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * Get the reason message.
     *
     * @return string The reason message.
     */
    abstract public function reason();

    private $path;
}
