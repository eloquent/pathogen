# Pathogen

*General-purpose path library for PHP.*

[![Build Status]](http://travis-ci.org/eloquent/pathogen)
[![Test Coverage]](http://lqnt.co/pathogen/artifacts/tests/coverage/)

## Installation

Available as [Composer](http://getcomposer.org/) package
[eloquent/pathogen](https://packagist.org/packages/eloquent/pathogen).

## What is Pathogen?

Pathogen is a library for path manipulation. Pathogen supports file system paths
including Unix and Windows style paths, but is truly a general-purpose path
implementation, capable of representing URI paths and other path-like structures
while providing a comprehensive API.

## Basic concepts

### Path atoms

In Pathogen, a path consists of a sequence of "atoms". Atoms are the individual
sections of the path hierarchy. Given the path `/path/to/foo`, the sequence of
atoms would be `path`, `to`, `foo`. The slash character is referred to as the
"separator".

The atoms `.` and `..` have special meaning in Pathogen. The single dot (`.`) is
referred to as the "self atom" and is typically used to reference the current
path. The double dot (`..`) is referred to as the "parent atom" and is used to
reference the path above the current one. Anyone familiar with typical file
system paths should be familiar with their behaviour already.

### Absolute vs. relative paths

In Pathogen, absolute and relative paths are represented by two different
classes. While both classes implement a common `PathInterface`, other methods
are provided by the `AbsolutePathInterface` or the `RelativePathInterface`
respectively.

This distinction provides, amongst other benefits, the ability to harness PHP's
type hinting to restrict the type of path required:

```php
use Eloquent\Pathogen\AbsolutePathInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\RelativePathInterface;

function anyPath(PathInterface $path)
{
    // accepts any path
}

function absoluteOnly(AbsolutePathInterface $path)
{
    // accepts only absolute paths
}

function relativeOnly(AbsolutePathInterface $path)
{
    // accepts only relative paths
}
```

### The "root" path

The "root" path is considered the top-most absolute path, and is represented as
a single separator with no atoms (`/`).

### The "self" path

The "self" path is considered to point to the "current" path, and is represented
as a single "self" atom (`.`).

### Path normalization

Normalization of a path is the process of converting a path to its simplest or
"canonical" form. This means resolving as many of the "self" and "parent" atoms
as possible. For example, the path `/path/to/foo/../bar` normalizes to
`/path/to/bar`.

Normalization works differently for absolute and relative paths. Absolute paths
can always be resolved to a definite form with no "self" or "parent" atoms.
Relative paths can often be simplified, but may still contain these special
atoms. For example, the path `../foo/../..` will actually normalize to `../..`.

Note that for absolute paths, the "root" path (`/`) is the top-most path to
which "parent" atoms will normalize. That is, paths with more parent atoms than
regular atoms, like `/..`, `/../..`, or `/foo/../..` will all normalize to be
the root path (`/`).

<!-- references -->
[Build Status]: https://raw.github.com/eloquent/pathogen/gh-pages/artifacts/images/icecave/regular/build-status.png
[Test Coverage]: https://raw.github.com/eloquent/pathogen/gh-pages/artifacts/images/icecave/regular/coverage.png
