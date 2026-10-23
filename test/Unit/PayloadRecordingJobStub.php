<?php

namespace Gblix\Tests\Unit;

/**
 * A plain job that records the payload the traits hand to handle().
 */
final class PayloadRecordingJobStub
{
    /** @var array<string, mixed>|null */
    public static ?array $received = null;

    public function handle(array $data): ModelStub
    {
        self::$received = $data;

        return ModelStub::query()->create(['name' => $data['name'] ?? null]);
    }
}
