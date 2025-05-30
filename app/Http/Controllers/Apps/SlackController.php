<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SlackUserService;
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

        $redirectUri = 'https://99e0-105-197-96-252.ngrok-free.app/slack/oauth/callback';

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

    public  function getUsers(){

        $users = User::where('is_slack_user', true)
            ->select('id', 'name', 'slack_name', 'slack_id','slack_avatar')
            ->get();
        return response()->json(['users' => $users]);

    }

    public function slackChat()
    {
        if (!session()->has('slack_access_token')) {
            return redirect('/')->with('error', 'Please connect to Slack first');
        }

        try {
            $slackService = new SlackUserService();
            $slackService->syncSlackUsers();

            $accessToken = session('slack_access_token');
            $channelsResponse = Http::withToken($accessToken)
                ->post('https://slack.com/api/conversations.list', [
                    'types' => 'public_channel,private_channel',
                    'limit' => 200
                ]);

            $usersResponse = Http::withToken($accessToken)
                ->get('https://slack.com/api/users.list');

            if (!$channelsResponse->json()['ok'] || !$usersResponse->json()['ok']) {
                throw new \Exception('Failed to fetch data from Slack');
            }

            $channels = collect($channelsResponse->json()['channels'] ?? [])
                ->map(function ($channel) {
                    return [
                        'id' => $channel['id'],
                        'name' => $channel['name'],
                        'is_private' => $channel['is_private'] ?? false,
                        'type' => 'channel'
                    ];
                });

            $users = collect($usersResponse->json()['members'] ?? [])
                ->reject(fn($user) => $user['is_bot'] || $user['id'] === 'USLACKBOT')
                ->map(function ($user) {
                    return [
                        'id' => $user['id'],
                        'name' => $user['real_name'] ?? $user['name'],
                        'avatar' => $user['profile']['image_48'] ?? null,
                        'type' => 'user'
                    ];
                });

            return view('app.slack.chat', [
                'teamName' => session('slack_team_name', 'Slack'),
                'initialData' => [
                    'channels' => $channels->values()->all(),
                    'users' => $users->values()->all()
                ]
            ]);

        } catch (\Exception $e) {
            return redirect('/')
                ->with('error', 'Failed to load chat: ' . $e->getMessage());
        }
    }

    public function getMessages(Request $request)
    {
        $request->validate([
            'conversation' => 'required|string',
            'type' => 'required|string|in:channel,user'
        ]);

        try {
            $channelId = $request->conversation;
            $isUser = $request->type === 'user';

            if ($isUser) {
                $openResponse = Http::withToken(session('slack_access_token'))
                    ->post('https://slack.com/api/conversations.open', [
                        'users' => $channelId
                    ]);

                $openData = $openResponse->json();
                if (!$openData['ok']) {
                    throw new \Exception($openData['error'] ?? 'Failed to open conversation');
                }

                $channelId = $openData['channel']['id'];
            }
            else {
                $joinResponse = Http::withToken(session('slack_access_token'))
                    ->post('https://slack.com/api/conversations.join', [
                        'channel' => $channelId
                    ]);

                $joinData = $joinResponse->json();

                if (!$joinData['ok'] && $joinData['error'] !== 'already_in_channel') {
                    throw new \Exception($joinData['error'] ?? 'Failed to join channel');
                }
            }

            $response = Http::withToken(session('slack_access_token'))
                ->get('https://slack.com/api/conversations.history', [
                    'channel' => $channelId,
                    'limit' => 50
                ]);

            $data = $response->json();

            if (!$data['ok']) {
                return response()->json([
                    'ok' => false,
                    'error' => $data['error'] ?? 'Unknown error'
                ]);
            }

            $userIds = collect($data['messages'])
                ->filter(function ($message) {
                    return isset($message['user']);
                })
                ->pluck('user')
                ->unique()
                ->values()
                ->all();

            $users = collect($userIds)
                ->mapWithKeys(function ($userId) {
                    $userResponse = Http::withToken(session('slack_access_token'))
                        ->get('https://slack.com/api/users.info', ['user' => $userId]);

                    $userData = $userResponse->json();
                    return [
                        $userId => [
                            'name' => $userData['user']['real_name'] ?? $userData['user']['name'],
                            'avatar' => $userData['user']['profile']['image_48'] ?? null
                        ]
                    ];
                });

            $messages = collect($data['messages'])
                ->map(function ($message) use ($users) {
                    $userInfo = isset($message['user']) ?
                        ($users[$message['user']] ?? ['name' => 'Unknown', 'avatar' => null]) :
                        ['name' => 'System', 'avatar' => null];

                    return [
                        'text' => $message['text'] ?? '',
                        'ts' => $message['ts'] ?? '',
                        'user' => $userInfo
                    ];
                });

            return response()->json([
                'ok' => true,
                'messages' => $messages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation' => 'required|string',
            'text' => 'required|string',
            'type' => 'required|in:channel,user'
        ]);

        try {
            $message = $request->text;

            // استبدال المنشنز بشكل صحيح
            $message = preg_replace_callback('/<@([^|>]+)\|([^>]+)>/', function($matches) {
                return "<@{$matches[1]}>";
            }, $message);

            $channelId = $request->conversation;

            if ($request->type === 'user') {
                $conversationResponse = Http::withToken(session('slack_access_token'))
                    ->post('https://slack.com/api/conversations.open', [
                        'users' => $channelId,
                        'return_im' => true
                    ]);

                $conversationData = $conversationResponse->json();

                if (!$conversationData['ok']) {
                    throw new \Exception($conversationData['error'] ?? 'Failed to open conversation');
                }

                if (empty($conversationData['channel']['is_im'])) {
                    throw new \Exception('Opened conversation is not a direct message');
                }

                $channelId = $conversationData['channel']['id'];
            }

            $messageData = [
                'channel' => $channelId,
                'text' => $message
            ];

            if (env('SLACK_BOT_NAME')) {
                $messageData['username'] = env('SLACK_BOT_NAME');
            }

            $response = Http::withToken(session('slack_access_token'))
                ->post('https://slack.com/api/chat.postMessage', $messageData);

            $responseData = $response->json();

            if (!$responseData['ok']) {
                throw new \Exception($responseData['error'] ?? 'Failed to send message');
            }

            return response()->json([
                'ok' => true,
                'message' => $responseData['message'] ?? null,
                'channel' => $channelId
            ]);

        } catch (\Exception $e) {
            \Log::error('Slack Message Failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
