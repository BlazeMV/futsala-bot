<?php

namespace App\TelegramCommands;

use Telegram\Bot\Commands\Command;
use App\ManagementEngine;

class HelpCommand extends Command
{
    protected $name = "help";

    protected $description = "Get a list of registered commands";

    public function handle($arguments)
    {
        $message = $this->getUpdate()->getMessage();
        $msg_sender = $message->getFrom();
        $msg_chat = $message->getChat();
    
        $manage = new ManagementEngine();
        $manage->updateUser($msg_sender);
        $manage->updateChat($msg_chat);
        $manage->attachChatUser($msg_chat->getId(), $msg_sender->getId());
        
        if (!starts_with($this->getUpdate()->getMessage()->getText(), '/help') && !starts_with($this->getUpdate()->getMessage()->getText(), '/listcommands')) {
            return 'unknown command!';
        }
        
        $commands = $this->getTelegram()->getCommands();
    
        $text = "List of registered commands\n\n";
        foreach ($commands as $name => $handler) {
            $text .= sprintf('/%s - %s'.PHP_EOL, $name, $handler->getDescription());
        }
    
        $this->replyWithMessage(['text' => $text, 'parse_mode' => 'HTML']);
    }
}
