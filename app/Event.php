<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Event extends Model
{
    protected $fillable = ['chat_id', 'time', 'location', 'step', 'team_turn', 'id'];
    
    public function Participants()
    {
        return $this->hasMany('App\Participant', 'event_id', 'id');
    }
    
    public function Chat()
    {
        return $this->belongsTo('App\Chat', 'chat_id', 'id');
    }

    public function delete()
    {
        foreach ($this->Participants as $Participant) {
            $Participant->delete();
        }
        foreach ($this->Teams as $team) {
            $team->delete();
        }
        parent::delete();
    }

    public function Teams()
    {
        return $this->hasMany('App\Team', 'event_id', 'id')->orderBy('event_team_id');
    }

    public function getLeaders()
    {
        $leaders = [];
        foreach ($this->Teams as $team) {
            $leaders[] = $team->Leader->User->id;
        }
        return $leaders;
    }

    public function getTeam1Participants()
    {
        return $this->getEligibleParticipants()->where('team_id', 1)->sortBy('id')->all();
    }

    public function getTeam2Participants()
    {
        return $this->getEligibleParticipants()->where('team_id', 2)->sortBy('id')->all();
    }

    public function getNoTeamParticipants()
    {
        return $this->getEligibleParticipants()->where('team_id', 0)->all();
    }

    public function nextTurn()
    {
        $this->team_turn = $this->team_turn + 1;
        if ($this->team_turn > 2) {
            $this->team_turn = 1;
        }
        $this->save();
        return $this->team_turn;
    }

    public function getEligibleParticipants()
    {
        $ret = $this->Participants->sortBy('id')->all();
        return Collection::make($ret);
    }
    
    public function getTurnTeam()
    {
        return $this->Teams->where('turn', $this->team_turn)->first();
    }

    public function getTeamedParticipants()
    {
        return $this->getEligibleParticipants()->whereIn('team_id', [1, 2])->sortBy('id')->all();
    }
}
