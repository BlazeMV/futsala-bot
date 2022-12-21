<?php

namespace App\TelegramCallbackQueries;

use App\Team;
use Telegram;
use App\Event;
use App\User;
use App\ManagementEngine;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class RefreshCallbackQuery extends Command
{
    protected $name = "refreshnoteam";

    protected $description = "";

    public function handle($arguments)
    {
        $cbq = $this->getUpdate()->getCallbackQuery();

        $event = Event::where('id', $arguments[0])->first();
        if ($event == null || $event->count() < 1) {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "Invalid event!"
            ]);
            return;
        }

        if ($event->step !== "team_select_time") {
            Telegram::answerCallbackQuery([
                'callback_query_id' => $cbq->getId(),
                'text' => "You cant Refresh this list at this time.!"
            ]);
            return;
        }
        
        $manage = new ManagementEngine();
        $num=1;
        $text = "Futsal match at " . $event->location . " " . $manage->getDateString($event->time) . " " . $manage->getTimeString($event->time) . "\n";
    
        if (count($event->getNoTeamParticipants()) > 0) {
            $text .= $event->getTurnTeam()->Leader->User->getTagableName() . "'s turn\n'";
        }
        
        
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
        
        if (count($event->getNoTeamParticipants()) < 1) {
            $keyboard = "";
            $text = "Final Team List:\n\nEvent group: " . $event->Chat->getGroupName() . "\n" . $text;

            foreach ($event->getTeamedParticipants() as $participant) {
                
                try {
                    Telegram::sendMessage([
                        'text' => $text,
                        'reply_markup' => $keyboard,
                        'parse_mode' => 'HTML',
                        'chat_id' => $participant->User->id
                    ]);
                } catch (\Exception $ex) {
                }
            }
            $event->delete();
        }
        
        $success = false;
        for ($i=0; $i < 3; $i++) {
            if (!$success) {
                try {
                    Telegram::sendMessage([
                        'chat_id' => $event->Chat->id,
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
        $success = false;
        if (!$success) {
            for ($i=0; $i < 2; $i++) {
                if (!$success) {
                    try {
                        Telegram::deleteMessage([
                            'chat_id' => $event->Chat->id,
                            'message_id' => $cbq->getMessage()->getMessageId(),
                        ]);
                        $success = true;
                    } catch (\Exception $ex) {
                        $success = false;
                    }
                }
            }
        }
        Telegram::answerCallbackQuery([
            'callback_query_id' => $cbq->getId(),
        ]);
    }
}
