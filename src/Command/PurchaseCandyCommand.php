<?php

namespace App\Command;

use App\Model\CandyCatalog;
use App\DTO\RequestTransactionDTO;
use App\Machine\MachineInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'purchase-candy', description: 'Purchase candy from the vending machine')]
final class PurchaseCandyCommand extends Command
{
    public const MAX_INPUT_QUANTITY = 100000;

    public function __construct(private readonly MachineInterface $machine, private readonly CandyCatalog $catalog)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        while (true) {
            $continue = $helper->ask($input, $output, new ConfirmationQuestion('Welcome, dear customer! Would you like to buy some candy? (y/n)'));
            if (!$continue) {
                return Command::SUCCESS;
            }

            $type = (string)$helper->ask($input, $output, $this->createTypeChoice($this->catalog->getList()));
            $itemCount = (int)$helper->ask($input, $output, $this->createQuestion('Please input packs of candy you want to buy (1..' . self::MAX_INPUT_QUANTITY . ', Default: 1)> ', 1, 'int'));
            $paymentAmount = (float)$helper->ask($input, $output, $this->createQuestion('Please input your payment amount> ', null, 'number'));

            $transaction = new RequestTransactionDTO($type, $itemCount, $paymentAmount);
            $result = $this->machine->execute($transaction);

            $unitPrice = $this->formatPrice($this->catalog->getPrice($result->getType()));
            $totalAmount = $this->formatPrice($result->getTotalAmount());

            $output->writeln("You bought <info>{$result->getItemQuantity()}</info> packs of <info>{$result->getType()}</info> for <info>{$totalAmount} €</info>, each for <info>$unitPrice €</info>");
            $output->writeln('Your change is:');
            $table = new Table($output);
            $table->setHeaders(['Coin', 'Count']);

            foreach ($result->getChange() as $coin => $count) {
                $table->addRow([$coin, $count]);
            }

            $table->render();
        }
    }

    private function formatPrice(float $price): string
    {
        return number_format($price, 2, ',', '');
    }

    private function createTypeChoice(array $choices): Question
    {
        $question = new ChoiceQuestion('Welcome, dear customer! Please select your favorite candy', $choices);
        $question->setErrorMessage('Candy selection %s is invalid.');

        return $question;
    }

    private function createQuestion(string $description, $default = null, ?string $type = null): Question
    {
        $question = new Question($description, $default);
        $question->setNormalizer(function ($value) {
            return is_string($value) ? trim($value) : $value;
        });
        $question->setValidator(function ($answer) use ($type) {
            if ($answer === null || $answer === '') {
                throw new InvalidArgumentException('Provide an input');
            }
            if ($type === 'int') {
                if (filter_var($answer, FILTER_VALIDATE_INT) === false) {
                    throw new InvalidArgumentException('Please enter a valid integer value.');
                }

                if ($answer <= 0) {
                    throw new InvalidArgumentException('Count must be greater than 0.');
                }

                if ($answer > self::MAX_INPUT_QUANTITY) {
                    throw new InvalidArgumentException('Count must not exceed ' . self::MAX_INPUT_QUANTITY . '.');
                }

                return (int)$answer;
            }
            if ($type === 'number') {
                if (!is_numeric($answer)) {
                    throw new InvalidArgumentException('Please enter a valid number (integer or decimal).');
                }

                return $answer + 0;
            }
            return $answer;
        });

        return $question;
    }
}