<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{

    public function slackView()
    {
        return view('slack');
    }


    public function index(Request $request)
    {
        $message = $request->message;

        $userMentions = [
            'karim' => 'U08TY53DBLK',
        ];

        $processedMessage = preg_replace_callback(
            '/@(\w+)/',
            function ($matches) use ($userMentions) {
                $username = strtolower($matches[1]);
                return isset($userMentions[$username]) ? "<@{$userMentions[$username]}>" : $matches[0];
            },
            $message
        );

        Http::post(env('LOG_SLACK_WEBHOOK_URL'), [
            'text' => $processedMessage,
            'username' => 'GOUDA',
            'icon_url' => 'https://media.licdn.com/dms/image/v2/D4D03AQE0a39az39yqA/profile-displayphoto-shrink_400_400/profile-displayphoto-shrink_400_400/0/1686239001026?e=1753920000&v=beta&t=aGB_9MhYQuPOgk4UuJtDILRsKBlhWampqDIh_mJforA',
        ]);

        return $processedMessage;
    }


    public function getSlackUsers()
    {
        $token = env('SLACK_API_TOKEN');
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('https://slack.com/api/users.list');

        $data = $response->json();
        dd($data);
    }
}
