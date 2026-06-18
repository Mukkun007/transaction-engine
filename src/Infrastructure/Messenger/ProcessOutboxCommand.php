<?php

namespace App\Infrastructure\Messenger;

use App\Application\Webhook\ProcessOutboxHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:outbox:process',
    description: 'Process pending outbox messages and send webhooks',
)]
final class ProcessOutboxCommand extends Command
{
    public function __construct(private ProcessOutboxHandler $handler)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Processing outbox messages...');
        $this->handler->process();
        $output->writeln('Done.');

        return Command::SUCCESS;
    }
}
