<?php

declare(strict_types=1);

use App\ChangeCalculators\GreedyChangeCalculator;
use App\Model\CandyVendingMachine;
use App\DTO\RequestTransactionDTO;
use App\Persistence\InMemoryCandyCatalog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CandyVendingMachine::class)]
final class CandyVendingMachineTest extends TestCase
{
    public static function successfulPurchaseProvider(): array
    {
        return [
            'lollipop with change' => ['lollipop', 2, 10.00, 5.98, ['2.00' => 2, '0.02' => 1]],
            'chewing gum exact' => ['chewing gum', 3, 5.97, 5.97, []],
        ];
    }

    #[DataProvider('successfulPurchaseProvider')]
    #[Test]
    public function testSuccessfulPurchases(string $type, int $quantity, float $paid, float $expectedTotal, array $expectedChange): void
    {
        $machine = new CandyVendingMachine(new GreedyChangeCalculator(), new InMemoryCandyCatalog());
        $dto = new RequestTransactionDTO($type, $quantity, $paid);

        $result = $machine->execute($dto);

        $this->assertSame($type, $result->getType());
        $this->assertSame($quantity, $result->getItemQuantity());
        $this->assertSame($expectedTotal, $result->getTotalAmount());
        $this->assertSame($expectedChange, $result->getChange());
    }

    #[Test]
    public function testInsufficientPaymentThrows(): void
    {
        $machine = new CandyVendingMachine(new GreedyChangeCalculator(), new InMemoryCandyCatalog());
        $dto = new RequestTransactionDTO('chewing gum', 3, 5.00);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient payment amount');
        $machine->execute($dto);
    }

    #[Test]
    public function testInvalidCandyTypeThrows(): void
    {
        $machine = new CandyVendingMachine(new GreedyChangeCalculator(), new InMemoryCandyCatalog());
        $dto = new RequestTransactionDTO('unknown-candy', 1, 1.00);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid candy name');
        $machine->execute($dto);
    }

    #[Test]
    public function testZeroQuantityResultsInFullChangeBack(): void
    {
        $machine = new CandyVendingMachine(new GreedyChangeCalculator(), new InMemoryCandyCatalog());
        $dto = new RequestTransactionDTO('lollipop', 0, 1.00);
        $result = $machine->execute($dto);

        $this->assertSame(0.0, $result->getTotalAmount());
        $this->assertSame(['1.00' => 1], $result->getChange());
    }

    #[Test]
    public function testNegativePaidAmountThrowsInsufficient(): void
    {
        $machine = new CandyVendingMachine(new GreedyChangeCalculator(), new InMemoryCandyCatalog());
        $dto = new RequestTransactionDTO('lollipop', 1, -1.00);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient payment amount');
        $machine->execute($dto);
    }

    #[Test]
    public function testRoundingAndChangeBreakdownForMinceDrops(): void
    {
        $machine = new CandyVendingMachine(new GreedyChangeCalculator(), new InMemoryCandyCatalog());
        $dto = new RequestTransactionDTO('mince drops', 3, 5.00);
        $result = $machine->execute($dto);

        $this->assertSame(2.07, $result->getTotalAmount());
        $this->assertSame([
            '2.00' => 1,
            '0.50' => 1,
            '0.20' => 2,
            '0.02' => 1,
            '0.01' => 1,
        ], $result->getChange());
    }
}
