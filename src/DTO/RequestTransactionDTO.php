<?php

declare(strict_types=1);

namespace App\DTO;

use App\Machine\PurchaseTransactionInterface;

readonly class RequestTransactionDTO implements PurchaseTransactionInterface
{
    public function __construct(
        private string $type,
        private int $itemQuantity,
        private float $paidAmount
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

    public function getPaidAmount(): float
    {
        return $this->paidAmount;
    }
}