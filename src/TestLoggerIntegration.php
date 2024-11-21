<?php

declare(strict_types=1);

namespace DobroSite\PHPUnit\PSR3;

/**
 * Интеграция {@see TestLogger} в тесты PHPUnit
 *
 * @since 1.0
 */
trait TestLoggerIntegration
{
    private ?TestLogger $testLogger = null;

    protected function getTestLogger(): TestLogger
    {
        if (!$this->testLogger instanceof TestLogger) {
            $this->testLogger = new TestLogger();
        }

        return $this->testLogger;
    }
}
