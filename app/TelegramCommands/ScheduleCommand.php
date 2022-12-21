<?php

namespace App\TelegramCommands;

use App\Chat;
use App\Event;
use App\User;
use Telegram\Bot\Commands\Command;
use App\ManagementEngine;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram;

class ScheduleCommand extends Command
{
    protected $name = "schedule";

    protected $description = "Schedule a futsal match. format = /schedule [location] [date] [time]";

    public function handle($arguments)
    {
        $message = $this->getUpdate()->getMessage();
        $msg_sender = $message->getFrom();
        $msg_chat = $message->getChat();
    
        $manage = new ManagementEngine();
        $manage->updateUser($msg_sender);
        $manage->updateChat($msg_chat);
        $manage->attachChatUser($msg_chat->getId(), $msg_sender->getId());
        
        $manage->addRoles();
        
        $chat = Chat::where('id', $msg_chat->getId())->first();
        
        if ($chat->authorized == 0) {
            $this->replyWithMessage((['text' => "Sorry. This chat is not authorized to use this bot. Please contact @BlazeMV", 'parse_mode' => 'HTML']));
            return;
        }
        
        $sender = User::where('id', $msg_sender->getId())->first();
        
        $isAdmin = false;
        foreach ($sender->Roles as $role) {
            if ($role->name == "Admin" || $role == "Permitted") {
                $isAdmin = true;
                break;
            }
        }
        if (!$isAdmin) {
            $this->replyWithMessage((['text' => "Sorry. You are not allowed to use this command in this chat.", 'parse_mode' => 'HTML']));
            return;
        }

        if (Event::where('chat_id', $msg_chat->getId())->count() > 0) {
            $this->replyWithMessage((['text' => "There is another ongoing event on this group.", 'parse_mode' => 'HTML']));
            return;
        }
        
        $msg_text = $message->getText();
        $parts = explode(' ', $msg_text);
        if (count($parts) !== 4) {
            $this->replyWithMessage((['text' => "Invalid format!\nformat = /schedule [location] [date] [time]", 'parse_mode' => 'HTML']));
            return;
        }
        $location = $parts[1];
        $time = strtotime($parts[2] . $parts[3]);
        
        if (!$manage->isFutureTime($time)) {
            $this->replyWithMessage(['text' => "Please provide a time 30 minutes ahead of current time.", 'parse_mode' => 'HTML']);
            return;
        }
        
        $event = Event::create([
            'chat_id' => $msg_chat->getId(),
            'time' => $time,
            'location' => $location
        ]);

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

        /*if ($event->chat_id == "-1001230715532") {
            $noPMAccess = [];
            foreach ($event->Chat->Users as $member) {
                try {
                    Telegram::sendMessage(['text' => $text, 'reply_markup' => $keyboard, 'parse_mode' => 'HTML', 'chat_id' => $member->id]);
                } catch (\Exception $ex) {
                    $noPMAccess[] = $member->getTagableName();
                }
            }

            $text = "Memebers who haven't started the bot in PM\n\n";
            foreach ($noPMAccess as $member) {
                $text .= $member . ", ";
            }
            $this->replyWithMessage(['text' => $text, 'parse_mode' => 'HTML']);
        }*/
    }
}
