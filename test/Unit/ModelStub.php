<?php

namespace Gblix\Tests\Unit;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class ModelStub extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'model_stubs';

    protected $guarded = [];

    public function scopeFilter(Builder $query, ?array $data): Builder
    {
        if (isset($data['name'])) {
            $query->where('name', $data['name']);
        }

        return $query;
    }
}
