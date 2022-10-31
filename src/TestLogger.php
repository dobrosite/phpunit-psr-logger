<?php

declare(strict_types=1);

namespace DobroSite\PHPUnit\PSR3;

use Psr\Log\AbstractLogger;

class TestLogger extends AbstractLogger
{
    private array $records = [];

    public function getRecords(): Records
    {
        return new Records($this->records);
    }

    public function log($level, string | \Stringable $message, array $context = []): void
    {
        $record = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        $this->records[] = $record;
    }
}
