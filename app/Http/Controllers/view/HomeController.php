<?php

namespace App\Http\Controllers\view;

use App\Chat;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index(){
        $chats = Chat::all();
        $users = User::all();
        return view('welcome', compact('chats', 'users'));
    }

    public function addRole(Request $request){
        $user = User::findOrFail($request->user_id);
        $user->Roles()->attach($request->role_id);
        $user->save();

        return redirect()->back();
    }

    public function authorizeChat(Request $request){
        $chat = Chat::findOrFail($request->chat);
        $chat->authorized = 1;
        $chat->save();
        return redirect()->back();
    }
}
