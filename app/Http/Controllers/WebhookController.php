<?php

namespace App\Http\Controllers;

use Telegram\Bot\Api;
use App\ManagementEngine;
use Telegram\Bot\Laravel\Facades\Telegram;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class WebhookController extends Controller
{
    public function getUpdates()
    {
        $update = Telegram::commandsHandler(true);
        if ($update->isType('callback_query')) {
            $cbq = $update->getCallbackQuery();
            $cbq_sender = $cbq->getFrom();
            $cbq_chat = $cbq->getMessage()->getChat();
        
            $manage = new ManagementEngine();
            $manage->updateUser($cbq_sender);
            $manage->updateChat($cbq_chat);
            $manage->attachChatUser($cbq_chat->getId(), $cbq_sender->getId());
        
            $data = $cbq->getData();
            $data = explode(' ', $data);
            try {
                $class = "App\TelegramCallbackQueries\\" . studly_case($data[0]) . "CallbackQuery";
                array_shift($data);
                $call = new $class;
                $call->make(new Api(config('telegram.bots.Futsala.token')), $data, $update);
            } catch (FatalThrowableError $ex) {
                return $ex->getMessage();
            }
        }
        return 'true';
    }
}
