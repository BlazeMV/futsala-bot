<?php

namespace App\TelegramCommands;

use App\ManagementEngine;
use Telegram\Bot\Commands\Command;
use Telegram;

class StartCommand extends Command
{
    protected $name = "start";

    protected $description = "Getting started!";

    public function handle($arguments)
    {
        $message = $this->getUpdate()->getMessage();
        $msg_sender = $message->getFrom();
        $msg_chat = $message->getChat();
    
        $manage = new ManagementEngine();
        $manage->updateUser($msg_sender);
        $manage->updateChat($msg_chat);
        $manage->attachChatUser($msg_chat->getId(), $msg_sender->getId());
        
        $text = "Hello " . $msg_sender->getFirstName() . ". Nice to meet you.\n\nSend /help to get a list of supported commands.";
        $this->replyWithMessage(['text' => $text, 'parse_mode' => 'HTML']);
    }
}
