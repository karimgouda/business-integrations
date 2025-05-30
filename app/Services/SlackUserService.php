<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SlackUserService
{
    public function syncSlackUsers()
    {
        $accessToken = session('slack_access_token');

        $response = Http::withToken($accessToken)
            ->get('https://slack.com/api/users.list');

        $users = $response->json()['members'] ?? [];

        foreach ($users as $slackUser) {
            if ($slackUser['is_bot'] || $slackUser['id'] === 'USLACKBOT') {
                continue;
            }

            User::updateOrCreate(
                ['slack_id' => $slackUser['id']],
                [
                    'name' => $slackUser['real_name'] ?? $slackUser['name'],
                    'email' => $slackUser['profile']['email'] ?? ($slackUser['id'] . '@slack.com'),
                    'slack_name' => $slackUser['name'],
                    'slack_avatar' => $slackUser['profile']['image_48'] ?? null,
                    'is_slack_user' => true,
                    'password' => bcrypt(Str::random(32))
                ]
            );
        }

        return true;
    }
}
