<?php

namespace App\TelegramCallbackQueries;

use App\Participant;
use App\Team;
use Telegram\Bot\Commands\Command;
use App\ManagementEngine;
use App\User;
use Telegram;
use App\Event;
use Telegram\Bot\Keyboard\Keyboard;

class ProceedCallbackQuery extends Command
{
    protected $name = "distribute";

    protected $description = "Whe Someone taps on Make Team";

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
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "Access Denied!"
            ]);
            return;
        }

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
                'text' => "You cant proceed to choosing leaders at this time.!"
            ]);
            return;
        }

        $event->step = "choose_leader_time";
        $event->save();
    
        $text = "Team Leaders\nTeam 1 - \nTeam 2 - \n2 Leaders, please select \"Make me a leader\". select \"Proceed to Team select\" when 2 leaders is chosen.";
        $keyboard = Keyboard::make()
            ->inline()
            ->row(
                Keyboard::inlineButton([
                    'text' => "Make me Team 1 leader",
                    'callback_data' => "make_leader " . $event->id . " 1"
                ])
            )->row(
                Keyboard::inlineButton([
                    'text' => "Make me Team 2 leader",
                    'callback_data' => "make_leader " . $event->id . " 2"
                ])
            )->row(
                Keyboard::inlineButton([
                    'text' => "Proceed to team select",
                    'callback_data' => "proceed_team_select " . $event->id
                ])
            );
        Telegram::sendMessage([
            'chat_id' => $event->Chat->id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => $keyboard
        ]);
        Telegram::answerCallbackQuery([
            'callback_query_id' => $cbq->getId(),
        ]);
        try{
            Telegram::deleteMessage([ 'chat_id' => $event->chat_id, 'message_id' => $cbq->getMessage()->getMessageId() ]);
        }catch (\Exception $exception){}
    }
}
