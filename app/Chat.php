<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = ['id', 'type', 'title', 'username', 'authorized'];
    
    public function Events(){
        return $this->hasMany('App\Event', 'chat_id', 'id');
    }
    
    public function Users(){
        return $this->belongsToMany('App\User', 'chat_user');
    }
    
    public function getGroupName(){
        if ($this->title !== ""){
            return $this->title;
        }else{
            return $this->username;
        }
    }
}
