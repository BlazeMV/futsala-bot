<?php

namespace App\TelegramCallbackQueries;

use App\Participant;
use App\Team;
use App\User;
use Telegram;
use App\Event;
use App\ManagementEngine;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class MakeLeaderCallbackQuery extends Command
{
    protected $name = "makeleader";

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
            if ($role->name == "Admin" || $role->name !== 'Permitted') {
                $isAdmin = true;
                break;
            }
        }
        if (!$isAdmin) {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "You are not Permitted to be a team leader!"
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
        if (Participant::where('event_id', $event->id)->where('user_id', $cbq_sender->getId())->first() == null) {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "You are not in on this event!"
            ]);
            return;
        }

        if ($event->step !== "choose_leader_time") {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "You cant promote yourself to a team leader at this time.!"
            ]);
            return;
        }

        $isLeader = false;
        foreach ($event->getLeaders() as $id) {
            if ($id == $cbq_sender->getId()) {
                $isLeader = true;
                break;
            }
        }
        if ($isLeader) {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "You are already a leader of other team!"
            ]);
            return;
        }
        $team = $event->Teams->where('event_team_id', $arguments[1])->first();
        if ($team !== null) {
            $team->delete();
        }

        $team = new Team();
        $team->event_team_id = $arguments[1];
        $team->event_id = $event->id;
        $team->leader_id = $cbq_sender->getId();
        $team->save();
        
        $leaders = ["", ""];
    
        $teams = Team::where('event_id', $event->id)->get();
        foreach ($teams as $team) {
            $leaders[$team->event_team_id - 1] = $team->Leader->User->getFullNameWithTag();
        }
    
        $text = "Team Leaders\nTeam 1 - $leaders[0]\nTeam 2 - $leaders[1]\n2 Leaders, please select \"Make me a leader\". select \"Proceed to Team select\" when 2 leaders is chosen.";
    
        $keyboard = Keyboard::make()->inline()->row(
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
    
        $success = false;
        for ($i=0; $i < 3; $i++) {
            if (!$success) {
                try {

                    $teams = Team::where('event_id', $event->id)->get();
                    foreach ($teams as $team) {
                        $leaders[$team->event_team_id - 1] = $team->Leader->User->getFullNameWithTag();
                    }

                    $text = "Team Leaders\nTeam 1 - $leaders[0]\nTeam 2 - $leaders[1]\n2 Leaders, please select \"Make me a leader\". select \"Proceed to Team select\" when 2 leaders is chosen.";

                    Telegram::EditMessageText([
                        'chat_id' => $event->Chat->id,
                        'message_id' => $cbq->getMessage()->getMessageId(),
                        'text' => $text,
                        'parse_mode' => 'HTML',
                        'reply_markup' => $keyboard
                    ]);
                    $success = true;
                } catch (\Exception $ex) {
                    $success = false;
                }
            }
        }

        Telegram::answerCallbackQuery([
            'callback_query_id' => $cbq->getId(),
        ]);
    }
}
