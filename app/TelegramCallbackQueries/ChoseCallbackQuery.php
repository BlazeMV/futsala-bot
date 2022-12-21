<?php

namespace App\TelegramCallbackQueries;

use App\Event;
use App\Participant;
use Telegram\Bot\Commands\Command;
use App\ManagementEngine;
use Telegram;

class ChoseCallbackQuery extends Command
{
    protected $name = "chose";

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
        
        $isALeader = false;
        foreach ($event->getLeaders() as $leader) {
            if ($leader == $cbq_sender->getId()) {
                $isALeader = true;
            }
        }
        if (!$isALeader) {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "Team members can only be chosen by a team leader!"
            ]);
            return;
        }
    
        if ($event->step !== "team_select_time") {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "Team member selection window is closed!!"
            ]);
            return;
        }
        
        $team = $event->getTurnTeam();
        
        if ($cbq_sender->getId() !== $team->leader_id) {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "Its not your turn to choose a member!"
            ]);
            return;
        }
        
        $member = $event->Participants->where('user_id', $arguments[1])->first();
        if ($member->team_id !== 0) {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "Looks like the selected participant is already a member of a team. refresh the list maybe?!"
            ]);
            return;
        }
        
        $member->team_id = $team->event_team_id;
        $member->save();
        $event->nextTurn();
        
        
        $call = new RefreshCallbackQuery();
        $call->make($this->getTelegram(), [$arguments[0]], $this->getUpdate());
    }
}
