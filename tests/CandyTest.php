<?php

declare(strict_types=1);

namespace tests;

use App\Persistence\InMemoryCandyCatalog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

#[CoversClass(InMemoryCandyCatalog::class)]
final class CandyTest extends TestCase
{
    #[Test]
    public function testGetListContainsAllExpectedCandies(): void
    {
        $catalog = new InMemoryCandyCatalog();
        $list = $catalog->getList();
        $expected = ['caramels', 'lollipop', 'mince drops', 'chewing gum', 'licorice'];

        // Ensure the list contains exactly these items (order not important)
        sort($list);
        sort($expected);
        $this->assertSame($expected, $list);
    }

    public static function priceProvider(): array
    {
        return [
            ['caramels', 4.99],
            ['lollipop', 2.99],
            ['mince drops', 0.69],
            ['chewing gum', 1.99],
            ['licorice', 3.59],
        ];
    }

    #[DataProvider('priceProvider')]
    #[Test]
    public function testGetPriceReturnsExpectedValue(string $candy, float $expected): void
    {
        $catalog = new InMemoryCandyCatalog();
        $this->assertEquals($expected, $catalog->getPrice($candy), '', 0.0001);
    }

    #[Test]
    public function testGetPriceInvalidCandyThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid candy name');
        $catalog = new InMemoryCandyCatalog();
        $catalog->getPrice('invalid');
    }
}
