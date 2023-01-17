<?php

namespace Gblix\Controllers\ApiTraits;

use Gblix\Repository\Contracts\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;

/**
 * Trait Read
 * @package Gblix\Controllers\ApiTraits
 *
 * @property RepositoryInterface $repository
 *
 * @property ?string $routeKey
 * @property ?string $resource
 */
trait Read
{
    /**
     * Find and retrieve the id of the current entry.
     *
     * @param Request $request
     * @return mixed The id in the db or false.
     */
    public function getCurrentEntryId(Request $request)
    {
        $route = $request->route();
        if (!$route instanceof Route) {
            return null;
        }
        $params = $route->originalParameters();

        $resource = $this->routeKey ?? $this->resource ?? null;

        if ($resource === null) {
            return Arr::last($params, static function ($param): bool {
                return is_string($param) || is_numeric($param);
            });
        }

        return $request->get($resource);
    }

    /**
     * Send additional criteria to read
     *
     * @param RepositoryInterface $repository
     *
     * @return void
     */
    protected function pushReadCriteria(RepositoryInterface $repository): void
    {
    }
}
