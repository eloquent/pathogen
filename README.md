# Pathogen

*General-purpose path library for PHP.*

[![The most recent stable version is 0.6.1][version-image]][Semantic versioning]
[![Current build status image][build-image]][Current build status]
[![Current coverage status image][coverage-image]][Current coverage status]

[build-image]: http://img.shields.io/travis/eloquent/pathogen/develop.svg "Current build status for the develop branch"
[Current build status]: https://travis-ci.org/eloquent/pathogen
[coverage-image]: http://img.shields.io/coveralls/eloquent/pathogen/develop.svg "Current test coverage for the develop branch"
[Current coverage status]: https://coveralls.io/r/eloquent/pathogen
[Semantic versioning]: http://semver.org/
[version-image]: http://img.shields.io/:semver-0.6.1-yellow.svg "This project uses semantic versioning"

## Installation and documentation

- Available as [Composer] package [eloquent/pathogen].
- [API documentation] available.

[API documentation]: http://lqnt.co/pathogen/artifacts/documentation/api/
[Composer]: http://getcomposer.org/
[eloquent/pathogen]: https://packagist.org/packages/eloquent/pathogen

## What is Pathogen?

*Pathogen* is a library for path manipulation. *Pathogen* supports file system
paths including Unix and Windows style paths, but is truly a general-purpose
path implementation, capable of representing URI paths and other path-like
structures while providing a comprehensive API.

## Table of contents

- [Pathogen concepts](#pathogen-concepts)
    - [Path parts](#path-parts)
        - [Path atoms](#path-atoms)
        - [Path name](#path-name)
        - [Path name extensions](#path-name-extensions)
        - [Trailing separators](#trailing separators)
    - [Absolute and relative paths](#absolute-and-relative-paths)
    - [Special paths](#special-paths)
    - [Creating paths](#creating-paths)
        - [Static factory methods](#static-factory-methods)
        - [Factory objects](#factory-objects)
    - [Path resolution](#path-resolution)
        - [Resolution methods](#resolution-methods)
        - [Resolver objects](#resolver-objects)
    - [Path normalization](#path-normalization)
        - [Normalize method](#normalize-method)
        - [Normalizer objects](#normalizer-objects)
    - [File system paths](#file-system-paths)
    - [Immutability of paths](#immutability-of-paths)
    - [Windows path support](#windows-path-support)
    - [Dependency consumer traits](#dependency-consumer-traits)
        - [Available dependency consumer traits](#available-dependency-consumer-traits)
- [Usage examples](#usage-examples)
    - [Resolving a user-provided path against the current working directory](#resolving-a-user-provided-path-against-the-current-working-directory)
    - [Determining whether one path exists inside another](#determining-whether-one-path-exists-inside-another)
    - [Appending an extension to a path](#appending-an-extension-to-a-path)
    - [Replacing a path's extension](#replacing-a-paths-extension)
    - [Replacing a section of a path](#replacing-a-section-of-a-path)

## Pathogen concepts

### Path parts

The overall structure of a *Pathogen* path can be broken down into smaller
parts. This diagram shows some of these named parts as they apply to a typical
path:

      A   A   ___ A ___
     / \ / \ /         \
    /foo/bar/baz.qux.pop
             \_________/
                name
    \__________________/
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

In *Pathogen*, a path consists of a sequence of 'atoms'. Atoms are the
individual sections of the path hierarchy. Given the path `/path/to/foo`, the
sequence of atoms would be `path`, `to`, `foo`. The slash character is referred
to as the 'separator'.

The atoms `.` and `..` have special meaning in *Pathogen*. The single dot (`.`)
is referred to as the 'self atom' and is typically used to reference the current
path. The double dot (`..`) is referred to as the 'parent atom' and is used to
reference the path above the current one. Anyone familiar with typical file
system paths should be familiar with their behaviour already.

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
example, given the path name `foo.bar.baz`, *Pathogen* can determine the 'name
without extension' (`foo.bar`), the 'name prefix' (`foo`), the 'name suffix'
(`bar.baz`), and the 'extension' (`baz`).

Given a path instance, the various sections can be retrieved as follows:

```php
$nameWithoutExtension = $path->nameWithoutExtension(); // returns a string
$namePrefix = $path->namePrefix(); // returns a string
$nameSuffix = $path->nameSuffix(); // returns a string or null
$extension = $path->extension(); // returns a string or null
```

#### Trailing separators

*Pathogen* is capable of representing a path with a trailing separator (`/`).
This is useful in the case that a trailing separator has a special meaning to
some logic, such as the behaviour of the Unix cp command. The trailing separator
support is purely for the use of developers utilizing *Pathogen*; it does not
affect any logic used by *Pathogen* itself.

It is worth noting that all new path instances produced by *Pathogen* will strip
any trailing slashes unless it is explicitly stated otherwise.

### Absolute and relative paths

In *Pathogen*, absolute and relative paths are represented by two different
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

[AbsolutePathInterface]: http://lqnt.co/pathogen/artifacts/documentation/api/Eloquent/Pathogen/AbsolutePathInterface.html
[PathInterface]: http://lqnt.co/pathogen/artifacts/documentation/api/Eloquent/Pathogen/PathInterface.html
[RelativePathInterface]: http://lqnt.co/pathogen/artifacts/documentation/api/Eloquent/Pathogen/RelativePathInterface.html

### Special paths

The 'root' path is considered the top-most absolute path, and is represented as
a single separator with no atoms (`/`).

The 'self' path is considered to point to the 'current' path, and is represented
as a single self atom (`.`).

### Creating paths

#### Static factory methods

The easiest way to create a *Pathogen* path is via the use of static factory
methods. To use this method effectively, simply choose the most appropriate
class for the type of path:

```php
use Eloquent\Pathogen\AbsolutePath;
use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Eloquent\Pathogen\Path;
use Eloquent\Pathogen\RelativePath;
use Eloquent\Pathogen\Unix\UnixPath;
use Eloquent\Pathogen\Windows\AbsoluteWindowsPath;
use Eloquent\Pathogen\Windows\WindowsPath;

$path = Path::fromString('/path/to/foo'); // absolute path
$path = Path::fromString('bar/baz');      // relative path

$path = AbsolutePath::fromString('/path/to/foo'); // only creates absolute paths
$path = RelativePath::fromString('bar/baz');      // only creates relative paths

$path = FileSystemPath::fromString('/path/to/foo');   // Unix path
$path = FileSystemPath::fromString('C:\path\to\foo'); // Windows path

$path = UnixPath::fromString('/path/to/foo');      // only creates Unix paths
$path = WindowsPath::fromString('C:\path\to\foo'); // only creates Windows paths

$path = AbsoluteWindowsPath::fromString('C:\path\to\foo'); // only creates absolute Windows paths
```

In addition to the `fromString()` method, there are other factory methods, some
common to all paths, others more specialized:

```php
use Eloquent\Pathogen\Path;
use Eloquent\Pathogen\Windows\AbsoluteWindowsPath;

// Equivalent to '/path/to/foo'
$path = Path::fromAtoms(array('path', 'to', 'foo'));

// Equivalent to 'C:\path\to\foo'
$path = AbsoluteWindowsPath::fromDriveAndAtoms(array('path', 'to', 'foo'), 'C');
```

#### Factory objects

*Pathogen* provides factory classes for creating paths. All path factories
implement [PathFactoryInterface] which allows a path to be created from various
kinds of input.

A simple example of path factory usage is as follows:

```php
use Eloquent\Pathogen\FileSystem\Factory\FileSystemPathFactory;

$factory = new FileSystemPathFactory;

$pathFoo = $factory->create('/path/to/foo');
$pathBar = $factory->create('C:/path/to/bar');
```

[PathFactoryInterface]: http://lqnt.co/pathogen/artifacts/documentation/api/Eloquent/Pathogen/Factory/PathFactoryInterface.html

### Path resolution

Resolution of a path involves taking a path which may be relative or absolute,
and figuring out where that path points to, given a known 'base' path. The
result of path resolution will always be an absolute path.

For example, consider a current path of `/path/to/foo`. A relative path of
`bar/baz`, will resolve to `/path/to/foo/bar/baz` against this path. Conversely,
an absolute path of `/path/to/qux` will not change after resolution, as it is
already an absolute path.

#### Resolution methods

The simplest way to achieve path resolution with *Pathogen* is to use the
most appropriate method on a path:

```php
use Eloquent\Pathogen\FileSystem\FileSystemPath;

$basePath = FileSystemPath::fromString('/path/to/foo');
$relativePath = FileSystemPath::fromString('bar/baz');
$absolutePath = FileSystemPath::fromString('/path/to/qux');

echo $basePath->resolve($relativePath); // outputs '/path/to/foo/bar/baz'
echo $basePath->resolve($absolutePath); // outputs '/path/to/qux'

echo $relativePath->resolveAgainst($basePath); // outputs '/path/to/foo/bar/baz'
```

#### Resolver objects

Path resolvers are also a standalone concept in *Pathogen*. A simple example of
their usage follows:

```php
use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Eloquent\Pathogen\Resolver\PathResolver;

$resolver = new PathResolver;

$basePath = FileSystemPath::fromString('/path/to/foo');
$relativePath = FileSystemPath::fromString('bar/baz');
$absolutePath = FileSystemPath::fromString('/path/to/qux');

echo $resolver->resolve($basePath, $relativePath); // outputs '/path/to/foo/bar/baz'
echo $resolver->resolve($basePath, $absolutePath); // outputs '/path/to/qux'
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

Normalization typically never takes place in *Pathogen* unless it is required
for a calculation, or done manually through the API. If a normalized path is
required for some reason, this is left to the developer to handle.

#### Normalize method

The simplest way to normalize a path is to use the `normalize()` method:

```php
use Eloquent\Pathogen\FileSystem\FileSystemPath;

$path = FileSystemPath::fromString('/path/./to/foo/../bar');

echo $path->normalize(); // outputs '/path/to/bar'
```

#### Normalizer objects

Path normalizers are also a standalone concept in *Pathogen*. A simple example
of their usage follows:

```php
use Eloquent\Pathogen\FileSystem\FileSystemPath;
use Eloquent\Pathogen\FileSystem\Normalizer\FileSystemPathNormalizer;

$normalizer = new FileSystemPathNormalizer;

$path = FileSystemPath::fromString('/path/./to/foo/../bar');

echo $normalizer->normalize($path); // outputs '/path/to/bar'
```

### File system paths

*Pathogen* provides support for dealing with file system paths in a *platform
agnostic* way. There are two approaches supported by *Pathogen*, which can be
applied depending on the situation.

The first approach is to inspect the path string and create an appropriate path
instance based upon a 'best guess'. This is handled by the [FileSystemPath]
class:

```php
use Eloquent\Pathogen\FileSystem\FileSystemPath;

$pathFoo = FileSystemPath::fromString('/path/to/foo');   // creates a Unix-style path
$pathBar = FileSystemPath::fromString('C:/path/to/bar'); // creates a Windows path
```

The second approach is to create paths based upon the current platform the code
is running under. That is, when running under Linux or Unix, create Unix-style
paths, and when running under Windows, create windows paths. This is handled by
the [PlatformFileSystemPath]:

```php
use Eloquent\Pathogen\FileSystem\PlatformFileSystemPath;

// creates a path to match the current platform
$path = PlatformFileSystemPath::fromString('/path/to/foo');
```

Note that [FileSystemPath] and [PlatformFileSystemPath] are only utility classes
with static methods. The actual path class used will depend on the input. If it
is necessary to type hint for a file system path, [FileSystemPathInterface] or
one of its more specialized child interfaces should be used instead.

[FileSystemPath]: http://lqnt.co/pathogen/artifacts/documentation/api/Eloquent/Pathogen/FileSystem/FileSystemPath.html
[FileSystemPathInterface]: http://lqnt.co/pathogen/artifacts/documentation/api/Eloquent/Pathogen/FileSystem/FileSystemPathInterface.html
[PlatformFileSystemPath]: http://lqnt.co/pathogen/artifacts/documentation/api/Eloquent/Pathogen/FileSystem/PlatformFileSystemPath.html

### Immutability of paths

Paths in *Pathogen* are *immutable*, meaning that once they are created, they
cannot be modified. When performing some mutating operation on a path, such as
normalization or resolution, a new path instance is produced, rather than the
original instance being altered. This allows a path to be exposed as part of an
interface without creating a leaky abstraction.

### Windows path support

*Pathogen* provides support for Windows paths. In addition to the methods
available to Unix-style paths, Windows paths contain an optional drive
specifier. The drive specifier is available via the `drive()` method:

```php
$drive = $path->drive(); // returns a single-character string, or null
```

### Dependency consumer traits

*Pathogen* provides some [traits] to make consuming its services extremely
simple for code targeting PHP 5.4 and higher.

The concept of a dependency consumer trait is simple. If a class requires, for
example, a path factory, it can simply use a `PathFactoryTrait`. This gives the
class `setPathFactory()` and `pathFactory()` methods for managing the path
factory dependency.

This example demonstrates how to use the file system path factory trait:

```php
use Eloquent\Pathogen\FileSystem\Factory\Consumer\FileSystemPathFactoryTrait;

class ExampleConsumer
{
    use FileSystemPathFactoryTrait;
}

$consumer = new ExampleConsumer;
echo get_class($consumer->pathFactory()); // outputs 'Eloquent\Pathogen\FileSystem\Factory\FileSystemPathFactory'
```

[traits]: http://php.net/traits

#### Available dependency consumer traits

- [PlatformFileSystemPathFactoryTrait](src/Eloquent/Pathogen/FileSystem/Factory/Consumer/PlatformFileSystemPathFactoryTrait.php)
- [FileSystemPathFactoryTrait](src/Eloquent/Pathogen/FileSystem/Factory/Consumer/FileSystemPathFactoryTrait.php)
- [PathFactoryTrait](src/Eloquent/Pathogen/Factory/Consumer/PathFactoryTrait.php)

## Usage examples

### Resolving a user-provided path against the current working directory

```php
use Eloquent\Pathogen\FileSystem\Factory\PlatformFileSystemPathFactory;

$factory = new PlatformFileSystemPathFactory;
$workingDirectoryPath = $factory->createWorkingDirectoryPath();

$path = $workingDirectoryPath->resolve(
    $factory->create($_SERVER['argv'][1])
);
```

### Resolving a path against another arbitrary path

```php
use Eloquent\Pathogen\Path;

$basePath = Path::fromString('/path/to/base');
$path = Path::fromString('../child');

$resolvedPath = $basePath->resolve($path);

echo $resolvedPath->string();              // outputs '/path/to/base/../child'
echo $resolvedPath->normalize()->string(); // outputs '/path/to/child'
```

### Determining whether one path exists inside another

```php
use Eloquent\Pathogen\Path;

$basePath = Path::fromString('/path/to/foo');
$pathA = Path::fromString('/path/to/foo/bar');
$pathB = Path::fromString('/path/to/somewhere/else');

var_dump($basePath->isAncestorOf($pathA)); // outputs 'bool(true)'
var_dump($basePath->isAncestorOf($pathB)); // outputs 'bool(false)'
```

### Appending an extension to a path

```php
use Eloquent\Pathogen\Path;

$path = Path::fromString('/path/to/foo.bar');
$pathWithExtension = $path->joinExtensions('baz');

echo $pathWithExtension->string(); // outputs '/path/to/foo.bar.baz'
```

### Replacing a path's extension

```php
use Eloquent\Pathogen\Path;

$path = Path::fromString('/path/to/foo.bar');
$pathWithNewExtension = $path->replaceExtension('baz');

echo $pathWithNewExtension->string(); // outputs '/path/to/foo.baz'
```

### Replacing a section of a path

```php
use Eloquent\Pathogen\Path;

$path = Path::fromString('/path/to/foo/bar');
$pathWithReplacement = $path->replace(1, array('for', 'baz'), 2);

echo $pathWithReplacement->string(); // outputs '/path/for/baz/bar'
```
