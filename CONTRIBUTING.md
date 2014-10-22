# Contributing

**Pathogen** is open source software; contributions from the community are
encouraged. Please take a moment to read these guidelines before submitting
changes.

## Code style

All PHP code must adhere to the [PSR-2] standards.

[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md

## Branching and pull requests

As a guideline, please follow this process:

1. [Fork the repository].
2. Create a topic branch for the change, branching from **develop**
(`git checkout -b branch-name develop`).
3. Make the relevant changes.
4. [Squash] commits if necessary (`git rebase -i develop`).
5. Submit a pull request to the **develop** branch.

[Fork the repository]: https://help.github.com/articles/fork-a-repo
[Squash]: http://git-scm.com/book/en/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages

## Tests and API documentation

This project uses [Archer] for testing, and API documentation generation:

- Run the tests with `vendor/bin/archer t path/to/tests`, or simply
  `vendor/bin/archer` to run the entire suite.
- Generate a coverage report with `vendor/bin/archer c path/to/tests`, or simply
  `vendor/bin/archer c` to generate coverage for the entire suite. Add `-o` to
  open the report in your browser.
- Generate API documentation with `vendor/bin/archer d`. Serve with
  `php -S localhost:8000 -t artifacts/documentation/api/`, and open your browser
  to [http://localhost:8000/].

For more detailed usage, see the [Archer documentation].

[Archer]: https://github.com/IcecaveStudios/archer
[Archer documentation]: https://github.com/IcecaveStudios/archer
[http://localhost:8000/]: http://localhost:8000/
