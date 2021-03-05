<?php

namespace Gblix\Validators;

use Illuminate\Validation\ValidationException;
use Prettus\Validator\LaravelValidator;

/**
 * Class BaseValidator
 * @package Gblix\Validators
 */
class BaseValidator extends LaravelValidator
{

    /**
     * Pass the data and the rules to the validator or throws ValidatorException
     *
     * @param string $action
     * @return boolean
     * @throws ValidationException
     */
    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw ValidationException::withMessages($this->errorsBag()->messages());
        }

        return true;
    }
}
