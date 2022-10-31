<?php

declare(strict_types=1);

namespace DobroSite\PHPUnit\PSR3;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class Records implements \ArrayAccess, \Countable
{
    private \ArrayObject $elements;

    private \Iterator $iterator;

    public function __construct(array $items)
    {
        $this->elements = new \ArrayObject($items);
        $this->iterator = $this->elements->getIterator();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function assert(array $constraints): self
    {
        if (!$this->getIterator()->valid()) {
            throw new ExpectationFailedException('There are not enough log records.');
        }

        $index = $this->getIterator()->key() + 1;
        $item = $this->getIterator()->current();
        \assert(\is_array($item));

        foreach ($constraints as $field => $constraint) {
            if ($constraint === null) {
                continue;
            }
            $this->applyConstraint($constraint, $field, $item, $index, $field);
        }

        $this->getIterator()->next();

        return $this;
    }

    public function count(): int
    {
        return $this->elements->count();
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function critical(mixed $message = null, array $context = null): self
    {
        return $this->assert(
            [
                'level' => 'critical',
                'message' => $message,
                'context' => $context,
            ]
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function debug(mixed $message = null, array $context = null): self
    {
        return $this->assert(
            [
                'level' => 'debug',
                'message' => $message,
                'context' => $context,
            ]
        );
    }

    public function delegate(callable $callback): self
    {
        $callback($this);

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function emergency(mixed $message = null, array $context = null): self
    {
        return $this->assert(
            [
                'level' => 'emergency',
                'message' => $message,
                'context' => $context,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function end(): void
    {
        if (!$this->getIterator()->valid()) {
            return;
        }
        $excess = count($this) - $this->getIterator()->key();
        if ($excess > 0) {
            throw new ExpectationFailedException(sprintf('Excessive log records: %d.', $excess));
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function error(mixed $message = null, array $context = null): self
    {
        return $this->assert(
            [
                'level' => 'error',
                'message' => $message,
                'context' => $context,
            ]
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function info(mixed $message = null, array $context = null): self
    {
        return $this->assert(
            [
                'level' => 'info',
                'message' => $message,
                'context' => $context,
            ]
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function notice(mixed $message = null, array $context = null): self
    {
        return $this->assert(
            [
                'level' => 'notice',
                'message' => $message,
                'context' => $context,
            ]
        );
    }

    public function offsetExists($offset): bool
    {
        \assert(\is_int($offset));

        return $this->elements->offsetExists($offset);
    }

    public function offsetGet($offset): mixed
    {
        \assert(\is_int($offset));

        return $this->elements->offsetGet($offset);
    }

    public function offsetSet($offset, $value): void
    {
    }

    public function offsetUnset($offset): void
    {
    }

    /**
     * @throws ExpectationFailedException
     */
    public function skip(int $count): self
    {
        for ($i = 0; $i < $count; ++$i) {
            if (!$this->getIterator()->valid()) {
                throw new ExpectationFailedException(
                    sprintf(
                        'Can not skip record #%d: the end of the log has been reached.',
                        count($this)
                    )
                );
            }
            $this->getIterator()->next();
        }

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function warning(mixed $message = null, array $context = null): self
    {
        return $this->assert(
            [
                'level' => 'warning',
                'message' => $message,
                'context' => $context,
            ]
        );
    }

    protected function getIterator(): \Iterator
    {
        return $this->iterator;
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    private function applyConstraint(
        mixed $constraint,
        string $field,
        array $item,
        int $index,
        string $label
    ): void {
        Assert::assertArrayHasKey(
            $field,
            $item,
            sprintf(
                'Record #%d: missing field "%s". Existed fields: %s.',
                $index,
                $label,
                implode(', ', array_keys($item))
            )
        );

        if (is_array($constraint)) {
            foreach ($constraint as $subField => $subConstraint) {
                $this->applyConstraint(
                    $subConstraint,
                    $subField,
                    $item[$field],
                    $index,
                    $label . '.' . $subField
                );
            }

            return;
        }

        if (!$constraint instanceof Constraint) {
            if (is_string($constraint) && str_starts_with($constraint, '/')) {
                $constraint = Assert::matchesRegularExpression($constraint);
            } else {
                $constraint = Assert::equalTo($constraint);
            }
        }

        Assert::assertThat(
            $item[$field],
            $constraint,
            sprintf(
                'Record #%d: value of the "%s" field does not match given constraint.',
                $index,
                $label
            )
        );
    }
}
