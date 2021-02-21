<?php

namespace Gblix\ServiceProviders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

/**
 * Class EloquentMacroServiceProvider
 * @package Gblix\ServiceProviders
 */
class EloquentMacroServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     *
     * @psalm-suppress UndefinedMethod
     * @psalm-suppress MissingClosureParamType
     * @psalm-suppress MissingClosureReturnType
     */
    public function boot()
    {
        Builder::macro('paginateNoLimit', function ($columns = ['*'], $pageName = 'page') {
            $query = $this;
            /* @var $query Builder */
            $model = $query->getModel();

            $total = $query->toBase()->getCountForPagination();
            $results = $model->newCollection();

            return $query->paginator($results, $total, -1, 1, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]);
        });

        Builder::macro('paginateAll', function ($columns = ['*'], $pageName = 'page') {
            $query = $this;
            /* @var $query Builder */
            $model = $query->getModel();

            $results = ($total = $query->toBase()->getCountForPagination())
                ? $query->get($columns)
                : $model->newCollection();

            return $query->paginator($results, $total, -1, 1, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]);
        });
    }
}
