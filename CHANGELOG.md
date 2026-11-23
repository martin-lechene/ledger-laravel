# Changelog

All notable changes to this project are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- GitHub Actions workflow running the test suite on PHP 8.2 to 8.4 with Laravel 12.
- Community files: SECURITY, CONTRIBUTING, CODE_OF_CONDUCT, issue and PR templates, Dependabot config.
- Package metadata in `composer.json` (keywords, support, homepage).

### Changed
- Minimum PHP raised to `^8.2` to match Laravel 12.
- README rewritten and checked against the code (removed non-existent Artisan commands).
- `composer.lock` is no longer tracked (library).

### Removed
- `minimum-stability: dev` from `composer.json`.
- Empty `Feature` test suite from `phpunit.xml` (no `tests/Feature` directory exists).
