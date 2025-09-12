<?php

declare(strict_types=1);

namespace App\ChangeCalculators;

interface CalculatorInterface
{
    /**
     * @return array<string,int>
     */
    public function calculate(int $amountCents): array;
}
