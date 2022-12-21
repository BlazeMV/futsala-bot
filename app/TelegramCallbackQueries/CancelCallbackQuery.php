<?php

namespace App\TelegramCallbackQueries;

use App\User;
use Telegram;
use App\Event;
use App\ManagementEngine;
use Telegram\Bot\Commands\Command;

class CancelCallbackQuery extends Command
{
    protected $name = "cancel";

    protected $description = "Cancels an event";

    public function handle($arguments)
    {
        $cbq = $this->getUpdate()->getCallbackQuery();
        $cbq_sender = $cbq->getFrom();
        $cbq_chat = $cbq->getMessage()->getChat();
        $manage = new ManagementEngine();

        $sender = User::where('id', $cbq_sender->getId())->first();
        
        $isAdmin = false;
        foreach ($sender->Roles as $role) {
            if ($role->name == "Admin") {
                $isAdmin = true;
                break;
            }
        }
        if (!$isAdmin) {
            $this->getTelegram()->answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "Access Denied!"
            ]);
            return;
        }

        $event = Event::where('id', $arguments[0])->first();
        if ($event == null) {
            //throw new \Exception("no event");
            $res = $this->getTelegram()->answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "Invalid event!"
            ]);
            //dd($res);
            return;
        }

        $event->delete();

        Telegram::editMessageText([
            'text' => "Event Deleted successfully",
            'chat_id' => $cbq_chat->getId(),
            'message_id' => $cbq->getMessage()->getMessageId(),'parse_mode' => 'HTML'
        ]);
    }
}
