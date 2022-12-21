<?php

namespace App\TelegramCallbackQueries;

use App\User;
use Telegram;
use App\Event;
use App\ManagementEngine;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class ProceedTeamSelectCallbackQuery extends Command
{
    protected $name = "proceedteamselect";

    protected $description = "";

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

        if ($event->step !== "choose_leader_time") {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "You cant proceed to team select at this time.!"
            ]);
            return;
        }

        if ($event->Teams->count() < 2) {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "2 Teams are not created yet! Get " . (string)(2 - $event->Teams->count()) . " more team leaders."
            ]);
            return;
        }
    
        $event->step = "team_select_time";
        $event->save();
        $next_turn = $event->nextTurn();

        $turn = random_int(1, 2);
        foreach ($event->Teams as $team) {
            $team->turn = $turn++;
            if ($turn > 2) {
                $turn = 1;
            }
            $team->save();
            $leader = $team->Leader;
            $leader->team_id = $team->event_team_id;
            $leader->save();
        }
        $num=1;
    
        $text = "Futsal match at " . $event->location . " " . $manage->getDateString($event->time) . " " . $manage->getTimeString($event->time) . "\nEvent group: " . $event->Chat->getGroupName() . "\n";
        
        $text .= $event->getTurnTeam()->Leader->User->getTagableName() . "'s turn\n";
        
        $text .= "Team Red:\n";
        foreach ($event->getTeam1Participants() as $participant) {
            $text .= $num++ . " - ";
            $text .= $participant->User->getFullNameWithTag();
            if ($event->Teams->where('event_team_id', 1)->first()->leader_id == $participant->user_id) {
                $text .= " [team leader]";
            }
            $text .= "\n";
        }
        $num=1;
        $text .= "\nTeam Black:\n";
        foreach ($event->getTeam2Participants() as $participant) {
            $text .= $num++ . " - ";
            $text .= $participant->User->getFullNameWithTag();
            if ($event->Teams->where('event_team_id', 2)->first()->leader_id == $participant->user_id) {
                $text .= " [team leader]";
            }
            $text .= "\n";
        }
    
        $keyboard = Keyboard::make()->inline();
        foreach ($event->getNoTeamParticipants() as $participant) {
            $keyboard->row(
                Keyboard::inlineButton([
                    'text' => $participant->User->getFullName(),
                    'callback_data' => "chose " . $event->id . " " . $participant->user_id
                ])
            );
        }
        $keyboard->row(
            Keyboard::inlineButton([
                'text' => "Refresh List",
                'callback_data' => "refresh " . $event->id
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
        try {
            Telegram::deleteMessage([ 'chat_id' => $event->chat_id, 'message_id' => $cbq->getMessage()->getMessageId() ]);
        } catch (\Exception $exception) {
        }
    }
}
