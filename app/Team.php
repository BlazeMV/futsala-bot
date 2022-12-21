<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['event_id', 'leader_id', 'turn', 'id'];
    
    public function Event()
    {
        return $this->belongsTo('App\Event', 'event_id', 'id');
    }

    public function Members()
    {
        return $this->hasMany('App\Participant', 'team_id', 'id');
    }

    public function Leader()
    {
        return $this->hasOne('App\Participant', 'user_id', 'leader_id');
    }
}
