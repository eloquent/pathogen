# Pathogen changelog

### 0.2.0 (2013-07-27)

* **[NEW]** All paths can now be normalized by calling `normalize()`, which
  takes an optional normalizer to use.
* **[NEW]** Unix paths now have first-class representations, and their own
  factory.
* **[IMPROVED]** File system paths will automatically normalize the result of
  the `parent()` method ([#21]).
* **[NEW]** Implemented dependency consumer traits ([#22]).
* **[NEW]** Implemented simple methods for matching strings and patterns in
  paths and path names ([#19]).

### 0.1.2 (2013-07-18)

* **[FIXED]** AbsolutePath::relativeTo() now works for paths with common
  suffixes ([#20]).

### 0.1.1 (2013-07-08)

* **[FIXED]** AbsolutePath::relativeTo() no longer works backwards ([#18]).

### 0.1.0 (2013-07-08)

* **[NEW]** Initial implementation.

<!-- References -->

[#18]: https://github.com/eloquent/pathogen/issues/18
[#19]: https://github.com/eloquent/pathogen/issues/19
[#20]: https://github.com/eloquent/pathogen/issues/20
[#21]: https://github.com/eloquent/pathogen/issues/21
[#22]: https://github.com/eloquent/pathogen/issues/22
