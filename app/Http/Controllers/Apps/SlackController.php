<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SlackController extends Controller
{
    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            return redirect('/')->with('error', 'Authorization code is missing');
        }

        $code = $request->code;
        $clientId = env('SLACK_CLIENT_ID');
        $clientSecret = env('SLACK_CLIENT_SECRET');

        $redirectUri = 'https://5b59-105-197-96-252.ngrok-free.app/slack/oauth/callback';

        try {
            $response = Http::asForm()->post('https://slack.com/api/oauth.v2.access', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'redirect_uri' => $redirectUri
            ]);

            $data = $response->json();

            if (!$data['ok']) {
                $errorMsg = $data['error'] ?? 'Unknown error';
                if (isset($data['response_metadata']['warnings'])) {
                    $errorMsg .= ' | Warnings: ' . implode(', ', $data['response_metadata']['warnings']);
                }

                return redirect('/')->with('error', "Slack connection failed: $errorMsg");
            }

            session([
                'slack_access_token' => $data['access_token'],
                'slack_user_id' => $data['authed_user']['id'] ?? null,
                'slack_team_id' => $data['team']['id'] ?? null,
                'slack_team_name' => $data['team']['name'] ?? null
            ]);

            return redirect('/slack/chat');

        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Failed to connect with Slack: ' . $e->getMessage());
        }
    }

    public function slackChat()
    {
        if (!session()->has('slack_access_token')) {
            return redirect('/')->with('error', 'Please connect to Slack first');
        }

        try {
            $accessToken = session('slack_access_token');

            $response = Http::withToken($accessToken)
                ->post('https://slack.com/api/conversations.list', [
                    'types' => 'public_channel,private_channel',
                    'limit' => 100
                ]);

            $data = $response->json();

            if (!$data['ok']) {
                throw new \Exception($data['error'] ?? 'Failed to fetch channels');
            }

            $channels = collect($data['channels'] ?? [])
                ->map(function ($channel) {
                    return [
                        'id' => $channel['id'],
                        'name' => $channel['name'],
                        'is_private' => $channel['is_private'] ?? false,
                        'member_count' => $channel['num_members'] ?? 0
                    ];
                })
                ->sortBy('name')
                ->values()
                ->all();

            return view('app.slack.chat', [
                'channels' => $channels,
                'teamName' => session('slack_team_name', 'Slack')
            ]);

        } catch (\Exception $e) {
            return redirect('/')
                ->with('error', 'Failed to load channels: ' . $e->getMessage());
        }
    }


    public function sendMessage(Request $request)
    {

        if (!session()->has('slack_access_token')) {
            return response()->json(['ok' => false, 'error' => 'Not connected to Slack']);
        }

        $request->validate([
            'channel' => 'required|string',
            'text' => 'required|string'
        ]);

        try {

            $response = Http::withToken(session('slack_access_token'))
                ->post('https://slack.com/api/chat.postMessage', [
                    'channel' => $request->channel,
                    'text' => $request->text,
                    'as_user' => true
                ]);
            return $response->json();

        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

}
