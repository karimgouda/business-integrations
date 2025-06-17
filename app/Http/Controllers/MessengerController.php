<?php

namespace App\Http\Controllers;

use Facebook\Facebook;
use Illuminate\Http\Request;

class MessengerController extends Controller
{
    public function verifyWebhook(Request $request)
    {
        $verifyToken = env('MESSENGER_VERIFY_TOKEN');

        if ($request->hub_mode === 'subscribe' &&
            $request->hub_verify_token === $verifyToken) {
            return response($request->hub_challenge, 200);
        }

        return response('Invalid verification token', 403);
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
