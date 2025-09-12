<?php

declare(strict_types=1);

namespace tests;

use App\ChangeCalculators\CalculatorInterface;
use App\ChangeCalculators\GreedyChangeCalculator;
use App\DTO\RequestTransactionDTO;
use App\Model\CandyVendingMachine;
use App\Persistence\InMemoryCandyCatalog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CandyVendingMachine::class)]
final class CandyVendingMachineAdditionalTest extends TestCase
{
    public static function allCandyTypesProvider(): array
    {
        $cases = [];
        $catalog = new InMemoryCandyCatalog();
        foreach ($catalog->getList() as $candy) {
            $qty = match ($candy) {
                'caramels' => 2,
                'lollipop' => 3,
                'mince drops' => 4,
                'chewing gum' => 2,
                'licorice' => 1,
                default => 1,
            };
            $unit = $catalog->getPrice($candy);
            $totalCents = (int)round($unit * 100) * $qty;
            $total = $totalCents / 100;
            $cases[$candy] = [$candy, $qty, $total, $total, []];
        }
        return $cases;
    }

    #[DataProvider('allCandyTypesProvider')]
    #[Test]
    public function testAllCandyTypesExactPaymentGivesNoChange(string $type, int $qty, float $paid, float $expectedTotal, array $expectedChange): void
    {
        $machine = new CandyVendingMachine(new GreedyChangeCalculator(), new InMemoryCandyCatalog());
        $dto = new RequestTransactionDTO($type, $qty, $paid);
        $result = $machine->execute($dto);

        $this->assertSame($expectedTotal, $result->getTotalAmount());
        $this->assertSame($expectedChange, $result->getChange());
    }

    #[Test]
    public function testExactPaymentProducesEmptyChange(): void
    {
        $machine = new CandyVendingMachine(new GreedyChangeCalculator(), new InMemoryCandyCatalog());
        $catalog = new InMemoryCandyCatalog();
        $dto = new RequestTransactionDTO('licorice', 2, (int)round($catalog->getPrice('licorice') * 100) * 2 / 100);
        $result = $machine->execute($dto);
        $this->assertSame([], $result->getChange());
    }

    #[Test]
    public function testVeryLargeQuantityDoesNotOverflowAndTotalsCorrect(): void
    {
        $machine = new CandyVendingMachine(new GreedyChangeCalculator(), new InMemoryCandyCatalog());
        $quantity = 100_000; // large but safe
        $catalog = new InMemoryCandyCatalog();
        $unitCents = (int)round($catalog->getPrice('caramels') * 100);
        $expectedTotal = ($unitCents * $quantity) / 100.0; // ensure float
        $dto = new RequestTransactionDTO('caramels', $quantity, $expectedTotal);
        $result = $machine->execute($dto);
        $this->assertSame($expectedTotal, $result->getTotalAmount());
        $this->assertSame([], $result->getChange());
    }

    #[Test]
    public function testPaidWithManyDecimalPlacesRoundsCorrectly(): void
    {
        $machine = new CandyVendingMachine(new GreedyChangeCalculator(), new InMemoryCandyCatalog());
        $dto = new RequestTransactionDTO('lollipop', 0, 1.005);
        $result = $machine->execute($dto);
        // 1.005 should round to 1.01 i.e., 1 euro and 1 cent
        $this->assertSame(0.0, $result->getTotalAmount());
        $this->assertSame([
            '1.00' => 1,
            '0.01' => 1,
        ], $result->getChange());
    }

    #[Test]
    public function testAlternateCalculatorStrategyIsUsed(): void
    {
        $fake = new class implements CalculatorInterface {
            public function calculate(int $amountCents): array
            {
                return ['FAKE' => $amountCents];
            }
        };

        $machine = new CandyVendingMachine($fake, new InMemoryCandyCatalog());
        $catalog = new InMemoryCandyCatalog();
        $unitCents = (int)round($catalog->getPrice('chewing gum') * 100);
        $dto = new RequestTransactionDTO('chewing gum', 1, $unitCents / 100);
        $result = $machine->execute($dto);
        $this->assertSame(['FAKE' => 0], $result->getChange());
    }
}
