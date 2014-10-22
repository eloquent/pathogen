# Pathogen changelog

## 0.6.1 (2014-10-22)

- **[FIXED]** Path factories handle excessive separators like '/A///B///C'
  ([#38] - thanks [@rixbeck]).

[#38]: https://github.com/eloquent/pathogen/issues/38
[@rixbeck]: https://github.com/rixbeck

## 0.6.0 (2014-02-19)

- **[BC BREAK]** Path resolvers refactored:
    - `PathResolverInterface` is for resolvers that don't require a base path or
      other information passed to `resolve()`.
    - `BasePathResolverInterface` is for resolvers that take a base path in
      `resolve()`.
    - `BasePathResolver` replaces `PathResolver`.
    - `FixedBasePathResolver` replaces `BoundPathResolver`.
- **[BC BREAK]** Class/interface deletions:
    - `AbstractAbsoluteFileSystemPath`
    - `AbstractRelativeFileSystemPath`
    - `NormalizingPathResolver` (just use `normalize()` after resolution)
    - `NormalizingPathResolverTrait` (just use `normalize()` after resolution)
    - `PathResolverTrait` (use `resolve()` method or an injected resolver class)
    - `WorkingDirectoryResolver` (see the readme for a replacement solution)
- **[BC BREAK]** Class/interface renames:
    - `UndefinedPathAtomException` -> `UndefinedAtomException`
- **[BC BREAK]** The following methods no longer accept an optional normalizer:
    - `PathInterface::parent()`
    - `PathInterface::normalize()`
    - `AbsolutePathInterface::isRoot()`
    - `AbsolutePathInterface::isParentOf()`
    - `AbsolutePathInterface::isAncestorOf()`
    - `AbsolutePathInterface::relativeTo()`
    - `RelativePathInterface::isSelf()`
- **[NEW]** Static factory methods available for all path types. Examples
  include:
    - `Path::fromString()`
    - `Path::fromAtoms()`
    - `WindowsPath::fromDriveAndAtoms()`
- **[NEW]** Implemented `AbsolutePathInterface::resolve()` and
  `RelativePathInterface::resolveAgainst` as alternatives to using a resolver
  instance.
- **[NEW]** Implemented `nameAtomAt()` and `nameAtomAtDefault()`.
- **[NEW]** Implemented `InvalidPathExceptionInterface` for all exceptions that
  involve a complete, but invalid path. Various new exceptions of this type may
  be thrown when appropriate.
- **[IMPROVED]** Improved Windows path support:
    - Relative paths now support drive specifiers (yes, it's a thing).
    - Drive-less absolute paths are now represented as a relative path (after
    all, it *is* relative to the current drive).
    - Absolute paths now always have a drive specifier.
    - Implemented new `WindowsPathInterface` methods `matchesDrive()` and
    `matchesDriveOrNull()`.
    - Implemented `WindowsBasePathResolver`.
- **[IMPROVED]** Path factories will now create relative paths by default,
  rather than absolute ones.
- **[IMPROVED]** `atomAt()` and `atomAtDefault()` now accept negative indices to
  indicate an offset from the last index.
- **[IMPROVED]** Extra care is taken to avoid trailing separators in all result
  paths.
- **[IMPROVED]** Extra care is taken to avoid constructing new instances of
  static dependencies. Should result in minor memory usage / performance
  improvements.
- **[MAINTENANCE]** General repository maintenance

## 0.5.0 (2013-10-28)

- **[NEW]** All paths now implement `atomAt()` and `atomAtDefault()` ([#28]).

[#28]: https://github.com/eloquent/pathogen/issues/28

## 0.4.0 (2013-10-18)

- **[NEW]** All paths now implement `toAbsolute()` and `toRelative()` ([#27]).

[#27]: https://github.com/eloquent/pathogen/issues/27

## 0.3.0 (2013-09-27)

- **[BC BREAK]** Constants moved from path interface into implementation class,
  so that they can be overridden.

## 0.2.1 (2013-07-29)

- **[FIXED]** Unix paths now return Unix paths as results ([#25]).

[#25]: https://github.com/eloquent/pathogen/issues/25

## 0.2.0 (2013-07-27)

- **[NEW]** All paths can now be normalized by calling `normalize()`, which
  takes an optional normalizer to use.
- **[NEW]** Unix paths now have first-class representations, and their own
  factory.
- **[IMPROVED]** File system paths will automatically normalize the result of
  the `parent()` method ([#21]).
- **[NEW]** Implemented dependency consumer traits ([#22]).
- **[NEW]** Implemented simple methods for matching strings and patterns in
  paths and path names ([#19]).

[#19]: https://github.com/eloquent/pathogen/issues/19
[#21]: https://github.com/eloquent/pathogen/issues/21
[#22]: https://github.com/eloquent/pathogen/issues/22

## 0.1.2 (2013-07-18)

- **[FIXED]** AbsolutePath::relativeTo() now works for paths with common
  suffixes ([#20]).

[#20]: https://github.com/eloquent/pathogen/issues/20

## 0.1.1 (2013-07-08)

- **[FIXED]** AbsolutePath::relativeTo() no longer works backwards ([#18]).

[#18]: https://github.com/eloquent/pathogen/issues/18

## 0.1.0 (2013-07-08)

- **[NEW]** Initial implementation.
