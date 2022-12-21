<?php

namespace App\TelegramCommands;

use Telegram\Bot\Commands\Command;
use Telegram;
use App\ManagementEngine;
use App\Chat;
use App\Event;
use Telegram\Bot\Keyboard\Keyboard;

class EventCommand extends Command
{
    protected $name = "event";

    protected $description = "Get details of ongoing event.";

    public function handle($arguments)
    {
        $message = $this->getUpdate()->getMessage();
        $msg_sender = $message->getFrom();
        $msg_chat = $message->getChat();
    
        $manage = new ManagementEngine();
        $manage->updateUser($msg_sender);
        $manage->updateChat($msg_chat);
        $manage->attachChatUser($msg_chat->getId(), $msg_sender->getId());
    
        $chat = Chat::where('id', $msg_chat->getId())->first();
    
        if ($chat->authorized == 0) {
            $this->replyWithMessage((['text' => "Sorry. This chat is not authorized to use this bot. Please contact @BlazeMV", 'parse_mode' => 'HTML']));
            return;
        }
    
        $event = Event::where('chat_id', $msg_chat->getId())->first();
        
        if ($event == null) {
            $this->replyWithMessage((['text' => "There is no ongoing event on this group.", 'parse_mode' => 'HTML']));
            return;
        }

        if ($event->step !== "join_time") {
            $this->replyWithMessage((['text' => "Join time for current time has ended!", 'parse_mode' => 'HTML']));
            return;
        }
        
        $event_id = $event->id;
    
        $text = $manage->getEventText($event_id);
        $keyboard = Keyboard::make()
            ->inline()
            ->row(
                Keyboard::inlineButton([
                    'text' => 'In',
                    'callback_data' => "in $event_id"
                ]),
                Keyboard::inlineButton([
                    'text' => 'Out',
                    'callback_data' => "out $event_id"
                ])
            )->row(
                Keyboard::inlineButton([
                    'text' => 'Refresh list',
                    'callback_data' => "refresh_participants $event_id"
                ])
            )->row(
                Keyboard::inlineButton([
                    'text' => 'Proceed to make teams',
                    'callback_data' => "Proceed $event_id"
                ])
            );
    
        $this->replyWithMessage(['text' => $text, 'reply_markup' => $keyboard, 'parse_mode' => 'HTML']);
    }
}
