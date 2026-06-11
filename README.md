# gblix/laravel-controller-repository-traits

Controller traits for Laravel that streamline building REST APIs on top of
[`prettus/l5-repository`](https://github.com/andersao/l5-repository). The package wires
controllers, repositories, Fractal presenters and validators together so each CRUD endpoint
becomes a one-liner with well-defined customization hooks.

## Version compatibility

| Package version | Laravel   | PHP   | prettus/l5-repository |
|-----------------|-----------|-------|-----------------------|
| 1.8.x           | 12, 13    | ≥ 8.3 | `^2.10 \|\| ^4.0`     |
| 1.7.x (beta)    | 12        | ≥ 8.3 | `^2.10`               |
| 1.6.x           | 11        | ≥ 8.3 | `^2.9.1`              |

> **Laravel 13 requires prettus/l5-repository 4.x** (2.x/3.x cap at `illuminate/* ^12`).
> On Laravel 12 either prettus major works, which lets you upgrade this package first and
> migrate to prettus 4.x at your own pace.

### Upgrading with prettus 4.x installed

prettus/l5-repository 4.0 bundles the `Prettus\Validator\*` classes inside the package itself.
**Do not require `prettus/laravel-validation` alongside l5-repository 4.x** — both define the
same `Prettus\Validator` namespace and composer will report ambiguous class resolution. Remove
`prettus/laravel-validation` from your app's `composer.json` when moving to prettus 4.x; no code
changes are needed (`Prettus\Validator\LaravelValidator` and friends keep the same API).

## Installation

```bash
composer require gblix/laravel-controller-repository-traits
```

Register the macro service provider (not auto-discovered) to enable the
`paginateNoLimit`/`paginateAll` Eloquent builder macros:

```php
// config/app.php (or bootstrap/providers.php on Laravel 11+)
Gblix\ServiceProviders\EloquentMacroServiceProvider::class,
```

## Usage

### Controller traits

Six traits under `Gblix\Controllers\ApiTraits` implement the default REST actions. They expect
the controller to expose a `$repository` property implementing
`Gblix\Repository\Contracts\RepositoryInterface`:

```php
use Gblix\Controllers\ApiTraits;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends Controller
{
    use ApiTraits\Retrieve;  // index()
    use ApiTraits\Read;      // getCurrentEntryId() + shared read hooks
    use ApiTraits\Show;      // show()
    use ApiTraits\Create;    // runStore() — you implement store()
    use ApiTraits\Update;    // runUpdate() — you implement update()
    use ApiTraits\Delete;    // destroy()

    protected UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function store(Request $request): Response
    {
        return $this->runStore($request, CreateUserAction::class);
    }

    public function update(Request $request): Response
    {
        return $this->runUpdate($request, UpdateUserAction::class);
    }
}
```

`Create`/`Update`/`Delete` dispatch a "job" object: Laravel Actions v2 (`asController()`),
Laravel Actions v1 (`actingAs()->run()`) and plain classes with `run()`/`handle(array $data)`
are all supported.

#### Index behavior (`Retrieve`)

`index()` paginates by default and honors the `limit` query parameter:

| `limit`        | Result                                                                     |
|----------------|----------------------------------------------------------------------------|
| _absent_       | `paginate()` with the default page size (`repository.pagination.limit`)    |
| `0`            | `paginateNoLimit()` — pagination metadata only, empty `data`                |
| `-1`           | `paginateAll()` — every record plus pagination metadata                     |
| any other int  | `paginate(limit)`                                                           |

If the repository's model defines `scopeFilter`, the request input is forwarded to it through
`EntityFilterCriteria` (a JSON `filter` query parameter is decoded automatically).

#### Customization hooks

| Hook | Trait | Purpose |
|------|-------|---------|
| `pushIndexCriteria($repository, $request)` | Retrieve | Extra criteria for `index` |
| `pushEntityRelations($repository, $request)` | Retrieve | Eager-load relations |
| `filterRequestToEntityFilter($request, $data)` | Retrieve | Mutate filter data |
| `willIndexPaginate()` | Retrieve | Return `false` to disable pagination |
| `getRetrievePresenter()` / `getShowPresenter()` / `getStorePresenter()` / `getUpdatePresenter()` | each | Per-action presenter override |
| `pushReadCriteria($repository)` | Read | Criteria shared by show/update/delete |
| `pushShowCriteria($repository)` / `pushDestroyCriteria($repository)` | Show / Delete | Per-action criteria |
| `$routeKey` / `$resource` properties | Read | Customize how the entry id is read from the route |

### Repository

Extend `Gblix\Repository\BaseRepository` (which extends
`Prettus\Repository\Eloquent\BaseRepository`):

```php
use Gblix\Repository\BaseRepository;

class UserRepository extends BaseRepository
{
    public function model()
    {
        return User::class;
    }

    public function presenter()
    {
        return UserPresenter::class;
    }

    public function validator()
    {
        return UserValidator::class;
    }
}
```

Extras on top of prettus: `exists($id)`, `cursor()`, `paginateNoLimit()`, `paginateAll()` and
`collectionPresenter()` (an optional presenter class used for collections).

### Presenter

Extend `Gblix\Presenters\FractalPresenter` and return a
[league/fractal](https://fractal.thephpleague.com/) transformer:

```php
use Gblix\Presenters\FractalPresenter;
use League\Fractal\TransformerAbstract;

class UserPresenter extends FractalPresenter
{
    public function getTransformer(): TransformerAbstract
    {
        return new UserTransformer();
    }
}
```

### Validator

Extend `Gblix\Validators\BaseValidator` (a `Prettus\Validator\LaravelValidator`).
`passesOrFail()` throws Laravel's own `Illuminate\Validation\ValidationException`, so failures
render as standard 422 responses:

```php
use Gblix\Validators\BaseValidator;
use Prettus\Validator\Contracts\ValidatorInterface;

class UserValidator extends BaseValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => ['name' => 'required'],
        ValidatorInterface::RULE_UPDATE => ['name' => 'sometimes|required'],
    ];
}
```

### Entity filter criteria

`Gblix\Repositories\Criteria\EntityFilterCriteria` forwards an array to the model's
`scopeFilter`:

```php
public function scopeFilter(Builder $query, ?array $data): Builder
{
    if (isset($data['name'])) {
        $query->where('name', 'like', "%{$data['name']}%");
    }

    return $query;
}
```

## Testing

All tests run inside Docker — nothing is executed on the host machine. One command runs every
supported combination (CI uses the same script):

```bash
./scripts/test-all.sh
```

| Matrix | PHP | Laravel | prettus/l5-repository |
|--------|-----|---------|-----------------------|
| `test-l13` | 8.4 | 13 | 4.x |
| `test-l12` | 8.3 | 12 | 4.x |
| `test-l12-prettus2` | 8.3 | 12 | 2.x |

Individual matrices can be run with `docker compose run --rm <matrix>`. The non-canonical
matrices use isolated `composer-<matrix>.json`/`.lock`/`vendor-<matrix>/` artifacts
(gitignored) so they never clobber the committed lock file.

## Releasing

1. Merge to `master` via pull request (CI runs both matrices).
2. Tag the release (`git tag 1.8.0 && git push origin 1.8.0`).
3. The `Release` workflow re-runs the matrices and creates the GitHub Release; Packagist syncs
   automatically.
