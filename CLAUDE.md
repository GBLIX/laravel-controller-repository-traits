# CLAUDE.md — maintenance guide

Package: `gblix/laravel-controller-repository-traits` — controller traits for Laravel REST APIs
built on `prettus/l5-repository`. Consumed primarily by `taycoind/intrasys` (48 modules use
these traits in every controller).

## Hard rules

- **All composer/test execution happens inside Docker** (`docker compose run --rm test-l12|test-l13`).
  Never run composer or phpunit on the host machine. GitHub Actions runners are fine.
- Releases are plain tags without a `v` prefix (`1.8.0`, `1.7.0-beta4`, …). Packagist syncs
  automatically from GitHub; the `Release` workflow creates the GitHub Release.

## Architecture map

```
src/Gblix/  (PSR-0 autoload: "Gblix\" => "src")
├── Controllers/ApiTraits/
│   ├── Retrieve.php  index(): limit semantics (null→paginate, 0→paginateNoLimit, -1→paginateAll),
│   │                 EntityFilterCriteria push when model has scopeFilter, presenter negotiation
│   ├── Read.php      getCurrentEntryId() (route params or $routeKey/$resource), pushReadCriteria hook
│   ├── Show.php      show()/runShow() → repository->find()
│   ├── Create.php    runStore(job): Laravel Actions v2 (asController) / v1 (actingAs) / plain
│   │                 run()+handle(); response 201
│   ├── Update.php    runUpdate(job, id): same job dispatch; response 200
│   └── Delete.php    destroy()/runDestroy(): repository->delete() or job; 204 on true/null
├── Repository/
│   ├── BaseRepository.php             extends Prettus\Repository\Eloquent\BaseRepository;
│   │                                  adds exists(), cursor(), collectionPresenter(),
│   │                                  paginateNoLimit(), paginateAll()
│   └── Contracts/RepositoryInterface.php  extends prettus RepositoryInterface + Presentable +
│                                          RepositoryCriteriaInterface
│   └── Contracts/NegociatesPresenterContentInterface.php  optional presenter-per-request hook
├── Repositories/Criteria/EntityFilterCriteria.php  implements prettus CriteriaInterface;
│                                                   calls $model->filter($data) (model scopeFilter)
├── Presenters/
│   ├── FractalPresenter.php  extends Prettus\Repository\Presenter\FractalPresenter; null-safe
│   │                         present(), resource() dispatch, getFractal()
│   └── Contracts/PresenterInterface.php  extends prettus PresenterInterface
├── Validators/BaseValidator.php  extends Prettus\Validator\LaravelValidator; passesOrFail()
│                                 throws Illuminate ValidationException (not prettus's)
└── ServiceProviders/EloquentMacroServiceProvider.php  Builder macros paginateNoLimit/paginateAll
                                                       (uses paginator() + getCountForPagination();
                                                       NOT auto-discovered — apps register manually)
```

Tests: `test/Unit/*Test.php` with stubs (`*Stub.php`) over orchestra/testbench + in-memory sqlite.
`test/TestCase.php` registers ClockworkServiceProvider (the traits call the `clock()` helper) and
EloquentMacroServiceProvider, and provides `setUpDatabase()` (creates `model_stubs` table).

## Prettus coupling (what to re-audit on a prettus major bump)

Direct extensions: `Gblix\Repository\BaseRepository` → `Prettus\Repository\Eloquent\BaseRepository`;
`Gblix\Presenters\FractalPresenter` → `Prettus\Repository\Presenter\FractalPresenter`;
`Gblix\Validators\BaseValidator` → `Prettus\Validator\LaravelValidator`.
Contracts extended: `RepositoryInterface`, `Presentable`, `RepositoryCriteriaInterface`,
`PresenterInterface`, `CriteriaInterface`, `Transformable`/`TransformableTrait` (tests).
Prettus methods the traits call: `resetCriteria`, `pushCriteria`, `skipPresenter`, `setPresenter`,
`all`, `paginate`, `find`, `delete`, `parserResult`, `model`, plus protected
`applyCriteria`/`applyScope`/`resetModel`/`resetScope` inside `Gblix\Repository\BaseRepository`.

### 2.10 → 4.0 audit findings (2026-06-11)

- `Prettus\Repository\*` source is **byte-identical** between v2.10.1 and 4.0.0 (verified via
  jsDelivr file diff of BaseRepository, FractalPresenter, all contracts, TransformableTrait).
- 4.0 drops the `prettus/laravel-validation` dependency and **vendors `Prettus\Validator\*`
  inside l5-repository** (`"Prettus\\Validator\\": "src/Prettus/Validator/"`). API unchanged;
  internal-only diffs ( `$errors` initialized as MessageBag in a new `AbstractValidator`
  constructor; `ValidatorException` message + `toArray()` tweaks).
- ⚠️ Apps must NOT require `prettus/laravel-validation` together with l5-repository 4.x —
  duplicate `Prettus\Validator` namespace → ambiguous class resolution.
- Hence the dual constraint `"prettus/l5-repository": "^2.10 || ^4.0"`: Laravel 12 works with
  either major; Laravel 13 forces 4.x (2.x/3.x cap at illuminate ^12).
- prettus 3.x is just 2.x with PHP 8.4 syntax fixes, still illuminate ≤12; it is intentionally
  excluded from the constraint (4.0 covers the same range plus 13).

## Dependency compatibility matrix (checked 2026-06-11)

| Dependency | Constraint | First Laravel-13-ready release |
|---|---|---|
| prettus/l5-repository | `^2.10 \|\| ^4.0` | 4.0.0 (illuminate ^8…^13, php ^8.2) |
| spatie/laravel-fractal | `^6.3` | 6.4.0 |
| lorisleiva/laravel-actions | `^2.8` | v2.10.1 (illuminate ^11\|^12\|^13, php ^8.2) |
| itsgoingd/clockwork | `^5.2` | any (no illuminate constraints) |
| orchestra/testbench (dev) | `^10.0 \|\| ^11.0` | v11 = Laravel 13 (php ^8.3); v10 = Laravel 12 |
| spatie/laravel-package-tools (dev) | `^1.92` | 1.93.1 |

Check constraints quickly: `curl -s https://repo.packagist.org/p2/<vendor>/<pkg>.json` and read
`require.illuminate/*` of the latest versions.

## Adding support for a future Laravel N

1. Check each dependency above for an illuminate `^N` release (packagist p2 API).
2. Bump `orchestra/testbench` to include the matching major (testbench major = Laravel major − 2... 
   historically: 10→L12, 11→L13; verify on packagist).
3. Add/adjust a matrix service in `docker-compose.yml` (+ PHP version) and the GitHub Actions
   matrix in `.github/workflows/tests.yml`.
4. If prettus needs a new major, re-run the audit above: diff the extended classes/contracts
   between the pinned and the new tag (jsDelivr works when GitHub is flaky:
   `https://cdn.jsdelivr.net/gh/andersao/l5-repository@<tag>/src/Prettus/...`).
5. Run both/all matrices in docker; update README compatibility table, CHANGELOG, tag.

## Test matrices

- **`./scripts/test-all.sh` runs everything** (build + all three matrices, same script CI uses).
- `docker compose run --rm test-l13` — PHP 8.4, testbench ^11 (Laravel 13), prettus 4.x.
  Canonical: updates the committed `composer.lock` and `vendor/`.
- `docker compose run --rm test-l12` — PHP 8.3, testbench ^10 (Laravel 12), prettus 4.x.
  Isolated artifacts: `composer-l12.json`/`.lock`/`vendor-l12/` (gitignored). Matrix selection
  works via `composer update --with "orchestra/testbench:<constraint>"` in `scripts/run-tests.sh`.
- `docker compose run --rm test-l12-prettus2` — PHP 8.3, Laravel 12 pinned to prettus `^2.10`
  via `PRETTUS_CONSTRAINT`, proving the dual constraint (verified 2026-06-11: prettus 2.10.1 +
  laravel-validation 1.8.0 → 24/24 OK).`

## Release process

1. Branch from `master`, PR back to `master` (CI must pass both matrices).
2. After merge: `git tag <version> && git push origin <version>`.
3. `.github/workflows/release.yml` re-runs tests and creates the GitHub Release;
   Packagist auto-syncs.

## Out-of-repo context

- The consuming app `taycoind/intrasys` plans its own prettus 2.10→4.0 migration separately
  (heavy: every controller across 48 modules uses these traits). This package's dual constraint
  exists precisely so the app can bump the package first, prettus later.
