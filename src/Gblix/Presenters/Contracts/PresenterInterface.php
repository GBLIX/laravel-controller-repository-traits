<?php

namespace Gblix\Presenters\Contracts;

use League\Fractal\Manager;
use Prettus\Repository\Contracts\PresenterInterface as BasePresenterInterfaceAlias;

interface PresenterInterface extends BasePresenterInterfaceAlias
{

    /**
     * @return Manager
     */
    public function getFractal(): Manager;

    /**
     * @return string
     */
    public function getResourceKeyItem(): string;

    /**
     * @return string
     */
    public function getResourceKeyCollection(): string;
}
