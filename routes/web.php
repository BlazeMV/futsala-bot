<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::post('webhook', 'WebhookController@getUpdates');

Route::get('/', 'HomeController@index');


/* View */
Route::prefix('/cms')->group(function () {
    Route::get('/', 'view\HomeController@index')->name('home');
    Route::post('/authorize', 'view\HomeController@authorizeChat')->name('authorize');
    Route::post('/addrole', 'view\HomeController@addRole')->name('addrole');

    Route::get('/events', function () {
        dd(\App\Event::with(['Participants', 'Chat', 'Teams'])->get()->toArray());
    });

    Route::get('/parts', function () {
        dd(\App\Participant::with(['Event', 'User', 'Team'])->get()->toArray());
    });

    Route::get('/teams', function () {
        dd(\App\Team::with(['Event', 'Members', 'Leader'])->get()->toArray());
    });
    
    Route::get('/qwer', function () {
        foreach (\App\Event::all() as $event) {
            $event->delete();
        }
    });

    Route::get('/chatuser', function () {
        /*foreach (\App\Chat::all() as $chat) {
            $chat->Users()->detach();
        }*/
        $data = [];
        foreach (\App\Chat::with('Users')->get() as $chat) {
            $data[$chat->title ?: $chat->username] = $chat->Users->toArray();
        }
        dd($data);
    });
});

Auth::routes();
