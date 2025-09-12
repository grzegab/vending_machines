<?php

declare(strict_types=1);

namespace App\Persistence;

use App\Model\CandyCatalog;
use InvalidArgumentException;

final class InMemoryCandyCatalog implements CandyCatalog
{
    private array $prices = [
        'caramels' => 4.99,
        'lollipop' => 2.99,
        'mince drops' => 0.69,
        'chewing gum' => 1.99,
        'licorice' => 3.59,
    ];

    /**
     * @return array<int, string>
     */
    public function getList(): array
    {
        return array_keys($this->prices);
    }

    public function getPrice(string $candyName): float
    {
        $key = strtolower($candyName);
        if (!array_key_exists($key, $this->prices)) {
            throw new InvalidArgumentException('Invalid candy name');
        }

        return $this->prices[$key];
    }
}
