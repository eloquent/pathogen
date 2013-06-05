# Pathogen

*General-purpose path library for PHP.*

[![Build Status]][Latest build]
[![Test Coverage]][Test coverage report]

## Installation and documentation

* Available as [Composer] package [eloquent/pathogen].
* [API documentation] available.

## What is Pathogen?

**Pathogen** is a library for path manipulation. **Pathogen** supports file
system paths including Unix and Windows style paths, but is truly a
general-purpose path implementation, capable of representing URI paths and other
path-like structures while providing a comprehensive API.

## Table of contents

- [Pathogen concepts](#pathogen-concepts)
    - [Path parts](#path-parts)
        - [Path atoms](#path-atoms)
        - [Path name](#path-name)
        - [Path name extensions](#path-name-extensions)
        - [Trailing separators](#trailing separators)
    - [Absolute and relative paths](#absolute-and-relative-paths)
    - [Special paths](#special-paths)
    - [Path factories](#path-factories)
    - [Path resolution](#path-resolution)
    - [Path normalization](#path-normalization)
    - [File system paths](#file-system-paths)
    - [Immutability of paths](#immutability-of-paths)
    - [Windows path support](#windows-path-support)
- [Usage examples](#usage-examples)
    - [Resolving a user-provided path against the current working directory](#resolving-a-user-provided-path-against-the-current-working-directory)
    - [Determining whether one path exists inside another](#determining-whether-one-path-exists-inside-another)
    - [Appending an extension to a path](#appending-an-extension-to-a-path)
    - [Replacing a path's extension](#replacing-a-paths-extension)

## Pathogen concepts

### Path parts

The overall structure of a **Pathogen** path can be broken down into smaller
parts. This diagram shows some of these named parts as they apply to a typical
path:

      A   A   ___ A ___
     / \ / \ /         \
    /foo/bar/baz.qux.pop
             \_________/
                name
    \___________________/
            path

    A = atom

The 'name' portion can be further broken down as follows:

      NWE    E
    /     \ / \
    baz.qux.pop
    \_/ \_____/
     NP   NS

    NWE = name without extension
      E = extension
     NP = name prefix
     NS = name suffix

#### Path atoms

In **Pathogen**, a path consists of a sequence of 'atoms'. Atoms are the
individual sections of the path hierarchy. Given the path `/path/to/foo`, the
sequence of atoms would be `path`, `to`, `foo`. The slash character is referred
to as the 'separator'.

The atoms `.` and `..` have special meaning in **Pathogen**. The single dot
(`.`) is referred to as the 'self atom' and is typically used to reference the
current path. The double dot (`..`) is referred to as the 'parent atom' and is
used to reference the path above the current one. Anyone familiar with typical
file system paths should be familiar with their behaviour already.

Given a path instance, the atoms of the path can be determined as follows:

```php
$atoms = $path->atoms(); // returns an array of strings
```

#### Path name

The 'name' section of a path is simply the last atom of a path. If a path has no
atoms, its name is an empty string. Given a path instance, the name of the path
can be determined like so:

```php
$name = $path->name(); // returns a string
```

#### Path name extensions

The name of a path can be further divided using extension separators (`.`). For
example, given the path name `foo.bar.baz`, **Pathogen** can determine the 'name
without extension' (`foo`), the 'name prefix' (`foo.bar`), the 'name suffix'
(`bar.baz`), and the 'extension' (`baz`).

Given a path instance, the various sections can be retrieved as follows:

```php
$nameWithoutExtension = $path->nameWithoutExtension(); // returns a string
$namePrefix = $path->namePrefix(); // returns a string
$nameSuffix = $path->nameSuffix(); // returns a string or null
$extension = $path->extension(); // returns a string or null
```

#### Trailing separators

**Pathogen** is capable of representing a path with a trailing separator (`/`).
This is useful in the case that a trailing separator has a special meaning to
some logic, such as the behaviour of the Unix cp command. The trailing separator
support is purely for the use of developers utilizing **Pathogen**; it does not
affect any logic used by **Pathogen** itself.

It is worth noting that all new path instances produced by **Pathogen** will
strip any trailing slashes unless it is explicitly stated otherwise.

### Absolute and relative paths

In **Pathogen**, absolute and relative paths are represented by two different
classes. While both classes implement a common [PathInterface], other methods
are provided by the [AbsolutePathInterface] or the [RelativePathInterface]
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

function relativeOnly(RelativePathInterface $path)
{
    // accepts only relative paths
}
```

### Special paths

The 'root' path is considered the top-most absolute path, and is represented as
a single separator with no atoms (`/`).

The 'self' path is considered to point to the 'current' path, and is represented
as a single self atom (`.`).

### Path factories

**Pathogen** provides factory classes for creating paths. All path factories
implement [PathFactoryInterface] which allows a path to be created from various
kinds of input. This is the typical way in which path instances are created,
although the path classes *can* be constructed directly if desired.

A simple example of path factory usage is as follows:

```php
use Eloquent\Pathogen\FileSystem\Factory\FileSystemPathFactory;

$factory = new FileSystemPathFactory;

$pathFoo = $factory->create('/path/to/foo');
$pathBar = $factory->create('C:/path/to/bar');
```

### Path resolution

Resolution of a path involves taking a path which may be relative or absolute,
and figuring out where that path points to, given a known 'base' path. The
result of path resolution will always be an absolute path.

For example, consider a current path of `/path/to/foo`. A relative path of
`bar/baz`, will resolve to `/path/to/foo/bar/baz` against this path. Conversely,
an absolute path of `/path/to/qux` will not change after resolution, as it is
already an absolute path.

Path resolution is achieved by passing a path through a path resolver, like so:

```php
use Eloquent\Pathogen\FileSystem\Factory\FileSystemPathFactory;
use Eloquent\Pathogen\Resolver\PathResolver;

$factory = new FileSystemPathFactory;
$resolver = new PathResolver;

$basePath = $factory->create('/path/to/foo');
$path = $factory->create('bar/baz');

$finalPath = $resolver->resolve($basePath, $path);
echo $finalPath->string(); // outputs '/path/to/foo/bar/baz'
```

### Path normalization

Normalization of a path is the process of converting a path to its simplest or
*canonical* form. This means resolving as many of the self and parent atoms as
possible. For example, the path `/path/to/foo/../bar` normalizes to
`/path/to/bar`.

Normalization works differently for absolute and relative paths. Absolute paths
can always be resolved to a canonical form with no self or parent atoms.
Relative paths can often be simplified, but may still contain these special
atoms. For example, the path `../foo/../..` will actually normalize to `../..`.

Note that for absolute paths, the root path (`/`) is the top-most path to which
parent atoms will normalize. That is, paths with more parent atoms than regular
atoms, like `/..`, `/../..`, or `/foo/../..` will all normalize to be the root
path (`/`).

Normalization typically never takes place in **Pathogen** unless it is required
for a calculation, or done manually through the API. If a normalized path is
required for some reason, this is left to the developer to handle:

```php
use Eloquent\Pathogen\FileSystem\Factory\FileSystemPathFactory;
use Eloquent\Pathogen\FileSystem\Normalizer\FileSystemPathNormalizer;

$factory = new FileSystemPathFactory;
$normalizer = new FileSystemPathNormalizer;

$path = $factory->create('/path/./to/foo/../bar');

$normalizedPath = $normalizer->normalize($path);
echo $normalizedPath->string(); // outputs '/path/to/bar'
```

### File system paths

**Pathogen** provides support for dealing with file system paths in a *platform
agnostic* way. There are two approaches supported by **Pathogen**, which can be
applied depending on the situation.

The first approach is to inspect the path string and create an appropriate path
instance based upon a 'best guess'. This is handled by the
[FileSystemPathFactory]:

```php
use Eloquent\Pathogen\FileSystem\Factory\FileSystemPathFactory;

$factory = new FileSystemPathFactory;

$pathFoo = $factory->create('/path/to/foo'); // creates a unix-style path
$pathBar = $factory->create('C:/path/to/bar'); // creates a windows path
```

The second approach is to create paths based upon the current platform the code
is running under. That is, when running under Linux or Unix, create unix-style
paths, and when running under Windows, create windows paths. This is handled by
the [PlatformFileSystemPathFactory]:

```php
use Eloquent\Pathogen\FileSystem\Factory\PlatformFileSystemPathFactory;

$factory = new PlatformFileSystemPathFactory;

$path = $factory->create('/path/to/foo'); // creates a path to match the current platform
```

### Immutability of paths

Paths in **Pathogen** are *immutable*, meaning that once they are created, they
cannot be modified. When performing some mutating operation on a path, such as
normalization or resolution, a new path instance is produced, rather than the
original instance being altered. This allows a path to be exposed as part of an
interface without creating a leaky abstraction.

### Windows path support

**Pathogen** provides support for most common usages of Windows paths. In
addition to the methods available to unix-style absolute paths, Windows absolute
paths contain an optional drive specifier. This example shows how to retrieve
the drive specifier from a path instance:

```php
$drive = $path->drive(); // returns a single-character string, or null
```

It is worth noting that **Pathogen** does *not* support drive specifiers for
relative Windows paths, only for absolute Windows paths.

## Usage examples

### Resolving a user-provided path against the current working directory

```php
use Eloquent\Pathogen\FileSystem\Factory\PlatformFileSystemPathFactory;
use Eloquent\Pathogen\FileSystem\Resolver\WorkingDirectoryResolver;

$pathFactory = new PlatformFileSystemPathFactory;
$pathResolver = new WorkingDirectoryResolver;

$path = $pathResolver->resolve(
    $pathFactory->create($_SERVER['argv'][1])
);
```

### Determining whether one path exists inside another

```php
use Eloquent\Pathogen\Factory\PathFactory;

$pathFactory = new PathFactory;

$basePath = $pathFactory->create('/path/to/foo');
$pathA = $pathFactory->create('/path/to/foo/bar');
$pathB = $pathFactory->create('/path/to/somewhere/else');

var_dump($basePath->isAncestorOf($pathA)); // outputs 'bool(true)'
var_dump($basePath->isAncestorOf($pathB)); // outputs 'bool(false)'
```

### Appending an extension to a path

```php
use Eloquent\Pathogen\Factory\PathFactory;

$pathFactory = new PathFactory;

$path = $pathFactory->create('/path/to/foo.bar');
$pathWithExtension = $path->joinExtensions('baz');

echo $pathWithExtension->string(); // outputs '/path/to/foo.bar.baz'
```

### Replacing a path's extension

```php
use Eloquent\Pathogen\Factory\PathFactory;

$pathFactory = new PathFactory;

$path = $pathFactory->create('/path/to/foo.bar');
$pathWithNewExtension = $path->replaceExtension('baz');

echo $pathWithNewExtension->string(); // outputs '/path/to/foo.baz'
```

<!-- References -->

[AbsolutePathInterface]: http://lqnt.co/pathogen/artifacts/documentation/api/Eloquent/Pathogen/AbsolutePathInterface.html
[API documentation]: http://lqnt.co/pathogen/artifacts/documentation/api/
[Build Status]: https://raw.github.com/eloquent/pathogen/gh-pages/artifacts/images/icecave/regular/build-status.png
[Composer]: http://getcomposer.org/
[eloquent/pathogen]: https://packagist.org/packages/eloquent/pathogen
[FileSystemPathFactory]: http://lqnt.co/pathogen/artifacts/documentation/api/Eloquent/Pathogen/FileSystem/Factory/FileSystemPathFactory.html
[Latest build]: http://travis-ci.org/eloquent/pathogen
[PathFactoryInterface]: http://lqnt.co/pathogen/artifacts/documentation/api/Eloquent/Pathogen/Factory/PathFactoryInterface.html
[PathInterface]: http://lqnt.co/pathogen/artifacts/documentation/api/Eloquent/Pathogen/PathInterface.html
[PlatformFileSystemPathFactory]: http://lqnt.co/pathogen/artifacts/documentation/api/Eloquent/Pathogen/FileSystem/Factory/PlatformFileSystemPathFactory.html
[RelativePathInterface]: http://lqnt.co/pathogen/artifacts/documentation/api/Eloquent/Pathogen/RelativePathInterface.html
[Test coverage report]: http://lqnt.co/pathogen/artifacts/tests/coverage/
[Test Coverage]: https://raw.github.com/eloquent/pathogen/gh-pages/artifacts/images/icecave/regular/coverage.png
