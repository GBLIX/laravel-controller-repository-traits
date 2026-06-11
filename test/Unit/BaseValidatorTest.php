<?php

namespace Gblix\Tests\Unit;

use Gblix\Tests\TestCase;
use Illuminate\Validation\ValidationException;
use Prettus\Validator\LaravelValidator;

/**
 * Also a regression guard for the prettus/l5-repository 2.x -> 4.x migration:
 * since 4.0 the Prettus\Validator classes are vendored inside l5-repository
 * instead of coming from the prettus/laravel-validation package.
 */
final class BaseValidatorTest extends TestCase
{
    public function testValidatorExtendsPrettusLaravelValidator(): void
    {
        $validator = $this->app->make(ValidatorStub::class);

        $this->assertInstanceOf(LaravelValidator::class, $validator);
    }

    public function testPassesOrFailReturnsTrueForValidData(): void
    {
        /** @var ValidatorStub $validator */
        $validator = $this->app->make(ValidatorStub::class);

        $this->assertTrue($validator->with(['name' => 'John'])->passesOrFail());
    }

    public function testPassesOrFailThrowsLaravelValidationException(): void
    {
        /** @var ValidatorStub $validator */
        $validator = $this->app->make(ValidatorStub::class);
        $validator->with([]);

        try {
            $validator->passesOrFail();
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('name', $exception->errors());
        }
    }
}
