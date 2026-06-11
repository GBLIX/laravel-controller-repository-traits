<?php

namespace Gblix\Tests\Unit;

use Gblix\Validators\BaseValidator;

class ValidatorStub extends BaseValidator
{
    protected $rules = [
        'name' => 'required',
    ];
}
