# Changelog

## 1.8.0 — 2026-06-11

### Added
- Laravel 13 support: `prettus/l5-repository` constraint widened to `^2.10 || ^4.0`
  (prettus 4.x supports `illuminate/* ^8.0…^13.0`; on Laravel 12 either major works).
- Real phpunit test suite covering the API traits (index/show/store/update/destroy),
  `BaseRepository` extras, the `paginateNoLimit`/`paginateAll` builder macros,
  `FractalPresenter` and `BaseValidator` (including a regression guard for the
  `Prettus\Validator` classes vendored into l5-repository 4.x).
- Docker-based test matrices (`docker compose run --rm test-l12|test-l13`) — PHP 8.3/Laravel 12
  and PHP 8.4/Laravel 13; no tooling required on the host machine.
- GitHub Actions CI (both matrices) and tag-triggered release workflow.
- README documentation and maintenance guide (CLAUDE.md).
- MIT license (LICENSE file + composer.json `license` field).

### Changed
- `orchestra/testbench` dev constraint bumped to `^10.0 || ^11.0`.

### Upgrade notes
- If your app moves to prettus/l5-repository 4.x, **remove `prettus/laravel-validation`** from
  your dependencies: 4.x bundles the `Prettus\Validator` namespace itself and having both
  installed causes ambiguous class resolution. No code changes are required.

## 1.7.0-beta4 and earlier

See the git history (`git log --oneline`) — Laravel 12 dependency updates and removal of
`graham-campbell/binput`, PHPStan and Psalm.
