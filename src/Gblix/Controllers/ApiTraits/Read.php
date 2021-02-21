<?php

namespace Gblix\Controllers\ApiTraits;

use Gblix\Repository\Contracts\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Trait Read
 * @package Gblix\Controllers\ApiTraits
 *
 * @property RepositoryInterface $repository
 */
trait Read
{

    /**
     * Find and retrieve the id of the current entry.
     *
     * @param Request $request
     * @return string|int The id in the db or false.
     */
    public function getCurrentEntryId(Request $request)
    {
        $route = $request->route();
        $params = $route->originalParameters();

        $resource = $this->routeKey ?? $this->resource ?? null;

        if (!$resource) {
            return Arr::last($params, static function ($param) {
                return is_string($param) || is_numeric($param);
            });
        }

        return $request->{$resource};
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
