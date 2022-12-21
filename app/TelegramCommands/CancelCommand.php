<?php

namespace App\TelegramCommands;

use App\User;
use Telegram;
use App\Event;
use App\ManagementEngine;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class CancelCommand extends Command
{
    protected $name = "cancel";

    protected $description = "cancel an event";

    public function handle($arguments)
    {
        $message = $this->getUpdate()->getMessage();
        $msg_sender = $message->getFrom();
        $msg_chat = $message->getChat();
    
        $manage = new ManagementEngine();
        $manage->updateUser($msg_sender);
        $manage->updateChat($msg_chat);
        $manage->attachChatUser($msg_chat->getId(), $msg_sender->getId());

        $sender = User::where('id', $msg_sender->getId())->first();
        
        $isAdmin = false;
        foreach ($sender->Roles as $role) {
            if ($role->name == "Admin" || $role == "Permitted") {
                $isAdmin = true;
                break;
            }
        }
        if (!$isAdmin) {
            $this->replyWithMessage((['text' => "Sorry. You are not allowed to use this command in this chat."]));
            return;
        }

        $events = Event::where('chat_id', $msg_chat->getId())->get();
        if ($events == null || $events->count() < 1) {
            $this->replyWithMessage(['text' => 'There are no events currently scheduled in this group!', 'parse_mode' => 'HTML']);
            return;
        }

        $keyboard = Keyboard::make()->inline();

        foreach ($events as $event) {
            $location = $event->location;
            $date_time = $manage->getDateString($event->time) . " " . $manage->getTimeString($event->time);
            $event_id = $event->id;
            $keyboard->row(
                Keyboard::inlineButton([
                    'text' => "$date_time at $location",
                    'callback_data' => "cancel $event_id"
                ])
            );
        }


        $this->replyWithMessage([
            'text' => "Select an event from below to cancel!",
            'reply_markup' => $keyboard,
            'parse_mode' => 'HTML'
        ]);
    }
}
