<?php

namespace Gblix\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class ModelStub extends Model implements Transformable
{
    use TransformableTrait;
}
