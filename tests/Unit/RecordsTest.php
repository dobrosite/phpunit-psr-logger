<?php

declare(strict_types=1);

namespace Tests\Unit;

use DobroSite\PHPUnit\PSR3\Records;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(Records::class)]
final class RecordsTest extends TestCase
{
    private Records $records;

    /**
     * @throws \Throwable
     */
    public function testAssertInvalidMessage(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            "Record #1: value of the \"message\" field does not match given constraint.\n" .
            "Failed asserting that two strings are equal."
        );
        $this->records->assert(['message' => 'foo']);
    }

    /**
     * @throws \Throwable
     */
    public function testContextFieldNotExist(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            'Record #1: missing field "context.baz". Existed fields: foo, bar.'
        );
        $this->records->assert(['context' => ['baz' => true]]);
    }

    /**
     * @throws \Throwable
     */
    public function testDelegate(): void
    {
        $this->records->delegate(
            static function (Records $collection): void {
                $collection
                    ->assert(['message' => 'Record 1.'])
                    ->assert(['message' => 'Record 2.']);
            }
        )
            ->end();
    }

    /**
     * @throws \Throwable
     */
    public function testMessageNotExist(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('There are not enough log records.');
        $this->records->skip(2)->assert([]);
    }

    /**
     * @throws \Throwable
     */
    public function testSkipAndEnd(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Excessive log records: 1.');

        $this->records
            ->skip(1)
            ->end();
    }

    /**
     * @throws \Throwable
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->records = new Records(
            [
                [
                    'message' => 'Record 1.',
                    'context' => [
                        'foo' => 'bar',
                        'bar' => 'baz',
                    ],
                ],
                [
                    'message' => 'Record 2.',
                ],
            ]
        );
    }
}
