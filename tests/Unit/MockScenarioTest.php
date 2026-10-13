<?php

declare(strict_types=1);

namespace CcCheckerBot\Tests\Unit;

use CcCheckerBot\Demo\MockScenario;
use PHPUnit\Framework\TestCase;

final class MockScenarioTest extends TestCase
{
    public function testAuthScenarioIsExplicitlySimulated(): void
    {
        $result = (new MockScenario())->result('auth');

        self::assertSame('AUTH DEMO', $result['title']);
        self::assertSame('SIMULATED_OK', $result['status']);
        self::assertStringContainsString('No external service', $result['message']);
    }

    public function testChargeScenarioDoesNotPerformTransaction(): void
    {
        $result = (new MockScenario())->result('charge');

        self::assertSame('CHARGE DEMO', $result['title']);
        self::assertSame('SIMULATED_OK', $result['status']);
        self::assertStringContainsString('No transaction', $result['message']);
    }

    public function testUnknownScenarioHasSafeFallback(): void
    {
        self::assertSame('SIMULATED_UNKNOWN', (new MockScenario())->result('unknown')['status']);
    }
}
