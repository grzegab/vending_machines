<?php

declare(strict_types=1);

namespace App\Model;

use App\ChangeCalculators\CalculatorInterface;
use App\Machine\MachineInterface;
use App\Machine\PurchasedItemInterface;
use App\Machine\PurchaseTransactionInterface;
use InvalidArgumentException;

readonly class CandyVendingMachine implements MachineInterface
{
    public function __construct(private CalculatorInterface $changeCalculator, private CandyCatalog $catalog)
    {
    }

    public function execute(PurchaseTransactionInterface $purchaseTransaction): PurchasedItemInterface
    {
        $unitPrice = $this->catalog->getPrice($purchaseTransaction->getType());
        $totalCents = $this->calculateTotalPriceCents($purchaseTransaction->getItemQuantity(), $unitPrice);
        $paidCents = $this->toCentsSafely($purchaseTransaction->getPaidAmount());
        $change = $this->calculateChange($paidCents, $totalCents);

        return new CandyPurchase(
            $purchaseTransaction->getType(),
            $purchaseTransaction->getItemQuantity(),
            $totalCents / 100,
            $change
        );
    }

    private function calculateTotalPriceCents(int $quantity, float $unitPrice): int
    {
        $unitPriceCents = $this->toCentsSafely($unitPrice);
        return $quantity * $unitPriceCents;
    }

    private function calculateChange(int $paidCents, int $totalCents): array
    {
        $changeCents = $paidCents - $totalCents;
        if ($changeCents < 0) {
            throw new InvalidArgumentException('Insufficient payment amount');
        }

        return $this->changeCalculator->calculate($changeCents);
    }

    private function toCentsSafely(float $amount): int
    {
        $normalized = number_format($amount, 2, '.', '');
        return (int) str_replace('.', '', $normalized);
    }
}