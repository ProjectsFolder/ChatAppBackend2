<?php

namespace App\Command;

use App\Service\ChatService;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearChatsCommand extends Command
{
    private $chatService;
    private $container;

    public function __construct(ChatService $chatService, ContainerInterface $container)
    {
        parent::__construct();
        $this->chatService = $chatService;
        $this->container = $container;
    }

    protected function configure() {
        $this->setName('app:clear-inactive-chats');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $inactive = $this->container->hasParameter('chat_lifetime') ?
            $this->container->getParameter('chat_lifetime') :
            3600;
        $result = $this->chatService->clearInactiveChats($inactive);
        if ($result) {
            $output->writeln("Inactive chats have been removed.");
        } else {
            $output->writeln("Inactive chats not found.");
        }
    }
}
