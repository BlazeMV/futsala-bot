<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = ['user_id', 'event_id', 'team_id'];
    
    public function Event()
    {
        return $this->belongsTo('App\Event', 'event_id', 'id');
    }
    
    public function User()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function Team()
    {
        return $this->belongsTo('App\Team', 'team_id', 'id');
    }
}
