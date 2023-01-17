<?php

namespace Gblix\Tests\Unit;

use League\Fractal\TransformerAbstract;

class TransformerStub extends TransformerAbstract
{
    public function transform(ModelStub $stub): array
    {
        return $stub->transform();
    }
}
