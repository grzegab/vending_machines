<?php

declare(strict_types=1);

namespace App\ChangeCalculators;

use InvalidArgumentException;

final class GreedyChangeCalculator implements CalculatorInterface
{
    public const DENOMINATIONS_INTEGER_CENTS = [
        200,
        100,
        50,
        20,
        10,
        5,
        2,
        1,
    ];

    /**
     * @inheritDoc
     */
    public function calculate(int $amountCents): array
    {
        if ($amountCents < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        $result = [];

        foreach (self::DENOMINATIONS_INTEGER_CENTS as $coin) {
            if ($coin <= 0) {
                continue;
            }

            $count = intdiv($amountCents, $coin);
            if ($count > 0) {
                $label = number_format($coin / 100, 2, '.', '');
                $result[$label] = $count;
                $amountCents -= $coin * $count;
            }

            if ($amountCents === 0) {
                break;
            }
        }

        return $result;
    }
}
