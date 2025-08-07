<?php

namespace App\Http\Controllers;

use Facebook\Facebook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessengerController extends Controller
{
    public function verifyWebhook(Request $request)
    {
        $verify_token = env('FB_MESSENGER_VERIFY_TOKEN');

        if ($request->input('hub.verify_token') === $verify_token) {
           Log::info('resave:'.$verify_token);
        }

        Log::info('resave:'.$verify_token);
    }

    public function handleWebhook(Request $request)
    {

        $data = $request->all();

        \Log::info('Webhook received:', $data);

        // Handle different webhook events
        if (isset($data['entry'][0]['messaging'][0])) {
            $event = $data['entry'][0]['messaging'][0];
            $this->processEvent($event);
        }

        return response('EVENT_RECEIVED', 200);
    }

    private function processEvent($event)
    {
        if (isset($event['message'])) {
            $this->handleMessage($event);
        } elseif (isset($event['postback'])) {
            $this->handlePostback($event);
        }
    }
}
