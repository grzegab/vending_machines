<?php

declare(strict_types=1);

namespace tests;

use App\ChangeCalculators\GreedyChangeCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(GreedyChangeCalculator::class)]
final class GreedyChangeCalculatorAdditionalTest extends TestCase
{
    #[Test]
    public function testAllDenominationsAreUsedForThreeEuroEightyEight(): void
    {
        $calc = new GreedyChangeCalculator();
        $this->assertSame([
            '2.00' => 1,
            '1.00' => 1,
            '0.50' => 1,
            '0.20' => 1,
            '0.10' => 1,
            '0.05' => 1,
            '0.02' => 1,
            '0.01' => 1,
        ], $calc->calculate(388));
    }

    public static function amountSamples(): array
    {
        return [
            'small' => [37],
            'medium' => [1234],
            'edge-zero' => [0],
            'large' => [98765],
        ];
    }

    #[DataProvider('amountSamples')]
    #[Test]
    public function testSumOfChangeEqualsRequestedAmount(int $amountCents): void
    {
        $calc = new GreedyChangeCalculator();
        $change = $calc->calculate($amountCents);

        $sum = 0;
        foreach ($change as $label => $count) {
            $sum += (int) round(((float) $label) * 100) * $count;
        }

        $this->assertSame($amountCents, $sum, 'Sum of change must equal requested amount');
    }
}
