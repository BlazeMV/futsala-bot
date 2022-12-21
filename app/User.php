<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'username', 'email', 'password', 'id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function Roles()
    {
        return $this->belongsToMany('App\Role', 'role_user');
    }
    
    public function Chats()
    {
        return $this->belongsToMany('App\Chat', 'chat_user');
    }
    
    public function Participants()
    {
        return $this->hasMany('App\Participant', 'user_id', 'id');
    }
    
    public function getFullName()
    {
        return $this->first_name . " " . $this->last_name;
    }
    
    public function getTagableName()
    {
        if ($this->username !== null) {
            return "@" . $this->username;
        } else {
            return "<a href='tg://user?id=" . $this->id . "'>" . $this->getFullName() . "</a>";
        }
    }
    
    public function getFullNameWithTag()
    {
        return "<a href='tg://user?id=" . $this->id . "'>" . $this->getFullName() . "</a>";
    }

    public static function hasRole($user, string $role)
    {
        return $user->roles()->where('name', $role)->exists();
    }
}
