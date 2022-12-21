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



Route::get('setwebhook', 'WebhookController@setWebhook');
Route::post('webhook', 'WebhookController@getUpdates');

Route::get('/', 'HomeController@index');


/* View */
Route::prefix('/cms')->group(function () {
    Route::get('/', 'view\HomeController@index')->name('home');
    Route::post('/authorize', 'view\HomeController@authorizeChat')->name('authorize');
    Route::post('/addrole', 'view\HomeController@addRole')->name('addrole');

    Route::get('/events', function () {
        dd(\App\Event::all());
    });
    Route::get('/parts', function () {
        dd(\App\Participant::all());
    });
    Route::get('/teams', function () {
        dd(\App\Team::all());
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
        foreach (\App\Chat::all() as $chat) {
            $data[] = $chat->Users();
        }
        dd($data);
    });
});

Auth::routes();
