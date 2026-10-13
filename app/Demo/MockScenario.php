<?php

declare(strict_types=1);

namespace CcCheckerBot\Demo;

final class MockScenario
{
    /** @return array{title:string,status:string,message:string} */
    public function result(string $scenario): array
    {
        return match ($scenario) {
            'auth' => [
                'title' => 'AUTH DEMO',
                'status' => 'SIMULATED_OK',
                'message' => 'This is a fixed local demonstration result. No external service was contacted.',
            ],
            'charge' => [
                'title' => 'CHARGE DEMO',
                'status' => 'SIMULATED_OK',
                'message' => 'This is a fixed local demonstration result. No transaction was performed.',
            ],
            default => [
                'title' => 'DEMO',
                'status' => 'SIMULATED_UNKNOWN',
                'message' => 'The selected demo is not available.',
            ],
        };
    }
}
