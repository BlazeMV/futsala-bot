<?php

namespace App\TelegramCallbackQueries;

use Telegram;
use App\Event;
use App\ManagementEngine;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class RefreshParticipantsCallbackQuery extends Command
{
    protected $name = "refreshparticipants";

    protected $description = "";

    public function handle($arguments)
    {
        $cbq = $this->getUpdate()->getCallbackQuery();
        $cbq_sender = $cbq->getFrom();
        $cbq_chat = $cbq->getMessage()->getChat();
        $manage = new ManagementEngine();

        $event = Event::where('id', $arguments[0])->first();
        if ($event == null || $event->count() < 1) {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "Invalid event!"
            ]);
            return;
        }

        if ($event->step !== "join_time") {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "Error refreshing the list!"
            ]);
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
        
        try {
            Telegram::editMessageText([
                'text' => $text,
                'chat_id' => $cbq_chat->getId(),
                'message_id' => $cbq->getMessage()->getMessageId(),
                'reply_markup' => $keyboard,
                'parse_mode' => 'HTML'
            ]);
        } catch (\Exception $ex) {
        }
        try {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
            ]);
        } catch (\Exception $ex) {
        }
    }
}
