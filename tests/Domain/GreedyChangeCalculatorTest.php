<?php

declare(strict_types=1);

use App\ChangeCalculators\GreedyChangeCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(GreedyChangeCalculator::class)]
final class GreedyChangeCalculatorTest extends TestCase
{
    #[Test]
    public function testZeroAmountReturnsEmptyArray(): void
    {
        $calc = new GreedyChangeCalculator();
        $this->assertSame([], $calc->calculate(0));
    }

    #[Test]
    public function testNonDivisibleIntermediateAmountHandledWithRemainder(): void
    {
        $calc = new GreedyChangeCalculator();
        $this->assertSame([
            '0.02' => 1,
            '0.01' => 1,
        ], $calc->calculate(3));
    }

    #[Test]
    public function testNegativeAmountThrows(): void
    {
        $calc = new GreedyChangeCalculator();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be negative');
        $calc->calculate(-1);
    }
}
