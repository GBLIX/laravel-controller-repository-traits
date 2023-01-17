<?php

namespace Gblix\Repository\Contracts;

use Illuminate\Http\Request;

/**
 * Class NegociatesPresenterContentInterface
 * @package Gblix\Repositories\Contracts
 */
interface NegociatesPresenterContentInterface
{
    /**
     * Define the presenter to be used based on request
     *
     * @param Request $request
     */
    public function negociatesPresenterContent(Request $request): void;
}
