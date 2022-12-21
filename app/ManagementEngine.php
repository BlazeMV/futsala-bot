<?php

namespace App;

use Illuminate\Support\Carbon;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\CallbackQuery;
use Telegram\Bot\Objects\User as TgUser;
use Telegram\Bot\Objects\Chat as TgChat;
use DateTime;
use Telegram;

class ManagementEngine
{
    public function updateUser(TgUser $user)
    {
        $this_user = User::where('id', $user->getId())->first();
        if ($this_user !== null) {
            $this_user->id = $user->getId();
            $this_user->first_name = $user->getFirstName();
            $this_user->last_name = $user->getLastName();
            $this_user->username = $user->getUsername();
            $this_user->save();
        } else {
            $new_user = new User();
            $new_user->id = $user->getId();
            $new_user->first_name = $user->getFirstName();
            $new_user->last_name = $user->getLastName();
            $new_user->username = $user->getUsername();
            $new_user->save();
        }
        return true;
    }
    
    public function updateChat(TgChat $chat)
    {
        $this_chat = Chat::where('id', $chat->getId())->first();
        if ($this_chat !== null) {
            $this_chat->id = $chat->getId();
            $this_chat->type = $chat->getType();
            $this_chat->title = $chat->getTitle();
            $this_chat->username = $chat->getUsername();
            $this_chat->save();
        } else {
            $new_chat = new Chat();
            $new_chat->id = $chat->getId();
            $new_chat->type = $chat->getType();
            $new_chat->title = $chat->getTitle();
            $new_chat->username = $chat->getUsername();
            $new_chat->save();
        }
    }

    public function attachChatUser($chat_id, $user_id)
    {
        $user = User::find($user_id);

        if ($user !== null) {
            $isAttached= false;
            foreach ($user->Chats as $chat) {
                if ($chat->id == $chat_id) {
                    $isAttached = true;
                    break;
                }
            }
            if (!$isAttached) {
                $user->Chats()->attach($chat_id);
            }
        }
    }

    public function getEventText($event_id)
    {
        $event = Event::where('id', $event_id)->first();
        $location = $event->location;
        $date_time = $this->getDateString($event->time) . " " . $this->getTimeString($event->time);
        $participant_count = $event->Participants->count();
        $text = "Scheduled Futsal Match\n$date_time at $location \nEvent group: " . $event->Chat->getGroupName() . "\n\nParticipants [$participant_count]:\n";
        $num = 1;
        foreach ($event->Participants as $participant) {
            $text .= $num . ". " . $participant->User->getFullNameWithTag() . "\n";
            $num++;
        }
        
        return $text;
    }
    
    public function updateEvents()
    {
    }
    
    public function getDateString($date)
    {
        $today = new DateTime(); // This object represents current date/time
        $today->setTime(0, 0, 0); // reset time part, to prevent partial comparison
    
        $match_date = new DateTime();
        $match_date->setTimestamp($date);
        //$match_date = DateTime::createFromFormat( "Y.m.d\\TH:i", $date );
        $match_date->setTime(0, 0, 0); // reset time part, to prevent partial comparison
    
        $diff = $today->diff($match_date);
        $diffDays = (integer)$diff->format("%R%a"); // Extract days count in interval
    
        switch ($diffDays) {
            case 0:
                return "Today";
                break;
            case +1:
                return "Tomorrow";
                break;
            default:
                return date("l jS \of F Y", $date);
        }
    }
    
    public function getTimeString($date)
    {
        $time = new DateTime();
        $time->setTimestamp($date);
        return $time->format('H:i');
    }
    
    public function addRoles()
    {
        if (Role::where('name', 'Admin')->first() == null) {
            $n = new Role();
            $n->name ="Admin";
            $n->save();
        }
        if (Role::where('name', 'Permitted')->first() == null) {
            $n = new Role();
            $n->name ="Permitted";
            $n->save();
        }
    }
    
    public function testPM($text, $chat_id)
    {
        try {
            Telegram::setAsyncRequest(false)->sendMessage([
                'chat_id' => $chat_id,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);
        } catch (TelegramResponseException $exception) {
            return false;
        }
        return true;
    }
    
    public function askToChooseMember(CallbackQuery $cbq, $event_id, User $other_lead=null, User $prev_choice=null)
    {
        $cbq_sender = $cbq->getFrom();
        $cbq_chat = $cbq->getMessage()->getChat();
        
        $text = "";
        if ($other_lead !== null) {
            $text .= $other_lead->getTagableName . " chose " . $prev_choice->getTagableName . "\n\n";
        }
        $text .= "Its your chance to choose a player.";
        
        $keyboard = Keyboard::make()->inline();
        $participants = Participant::where('event_id', $event_id)->get();
    
        foreach ($participants as $participant) {
            $text = $participant->User->getFullName();
            $keyboard->row(
                Keyboard::inlineButton([
                    'text' => $text,
                    'callback_data' => "chose $event_id "
                ])
            );
        }
        $result = Telegram::setAsyncRequest(true)->sendMessage([
            'chat_id' => $cbq_sender->getId(),
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => $keyboard
        ]);
        return true;
    }
    
    public function isFutureTime($time)
    {
        $check_time = Carbon::now()->addMinutes(30)->timestamp;
        if ($check_time > $time) {
            return false;
        } else {
            return true;
        }
    }
}
