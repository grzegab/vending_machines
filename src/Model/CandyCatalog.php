<?php

declare(strict_types=1);

namespace App\Model;

use InvalidArgumentException;

/**
 * Catalog/provider for candy types and prices.
 */
interface CandyCatalog
{
    /**
     * @return array<int, string> List of available candy names
     */
    public function getList(): array;

    /**
     * @throws InvalidArgumentException when candy name is unknown
     */
    public function getPrice(string $candyName): float;
}
