<?php

namespace App\TelegramCallbackQueries;

use Telegram;
use App\Event;
use App\Participant;
use App\ManagementEngine;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class InCallbackQuery extends Command
{
    protected $name = "in";

    protected $description = "join button clicked";

    public function handle($arguments)
    {
        $cbq = $this->getUpdate()->getCallbackQuery();
        $cbq_sender = $cbq->getFrom();
        $cbq_chat = $cbq->getMessage()->getChat();
        $manage = new ManagementEngine();

        $event = Event::where('id', $arguments[0])->first();
        if ($event == null) {
            //throw new \Exception("no event");
            $res = Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "Invalid event!"
            ]);
            //dd($res);
            return;
        }

        if ($event->step !== "join_time") {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "Sorry! Joining time for this event is over!"
            ]);
            return;
        }

        $isIn = false;
        foreach ($event->Participants as $participant) {
            if ($participant->User->id == $cbq_sender->getId()) {
                $isIn = true;
                break;
            }
        }
        if ($isIn) {
            //throw new \Exception("is in");
            $res = Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "You are already attending this event!"
            ]);
            //dd($res);
            return;
        }

        $participant = new Participant();
        $participant->user_id = $cbq_sender->getId();
        $participant->event_id = $event->id;
        $participant->save();

        $res = Telegram::answerCallbackQuery([
            'callback_query_id' => $cbq->getId(),
            'text' => "You have successfully joined this event!"
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
                    'text' => 'Proceed to Making Teams',
                    'callback_data' => "proceed $event_id"
                ])
            );
        $success = false;
        for ($i=0; $i < 3; $i++) {
            if (!$success) {
                try {
                    $text = $manage->getEventText($event_id);
                    Telegram::editMessageText([
                        'text' => $text,
                        'chat_id' => $cbq_chat->getId(),
                        'message_id' => $cbq->getMessage()->getMessageId(),
                        'reply_markup' => $keyboard, 'parse_mode' => 'HTML'
                    ]);
                    $success = true;
                } catch (\Exception $ex) {
                    $success = false;
                }
            }
        }
    }
}
