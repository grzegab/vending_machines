<?php

declare(strict_types=1);

namespace App\Model;

use App\Machine\PurchasedItemInterface;

readonly class CandyPurchase implements PurchasedItemInterface
{

    public function __construct(
        private string $type,
        private int $itemQuantity,
        private float $totalAmount,
        private array $change = []
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getItemQuantity(): int
    {
        return $this->itemQuantity;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    /**
     * @return array<float, int>
     */
    public function getChange(): array
    {
        return $this->change;
    }
}