@extends('layouts.master')
@push('css')
    <style>
        #messages-container {
            scroll-behavior: smooth;
            background-color: #f8fafc;
            padding: 1rem;
        }

        .message {
            margin-bottom: 1.5rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .message:hover {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .message-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .message-avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            margin-right: 0.75rem;
            object-fit: cover;
        }

        .message-avatar-placeholder {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            margin-right: 0.75rem;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4a5568;
            font-weight: bold;
        }

        .message-sender {
            font-weight: 600;
            color: #2d3748;
        }

        .message-time {
            font-size: 0.75rem;
            color: #718096;
            margin-left: 0.75rem;
        }

        .message-content {
            color: #4a5568;
            line-height: 1.5;
            white-space: pre-wrap;
        }

        #message-input {
            resize: none;
            min-height: 5rem;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            padding: 0.75rem;
            transition: all 0.2s;
        }

        #message-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        #send-button {
            transition: all 0.2s;
        }

        #send-button:hover {
            transform: translateY(-1px);
        }

        .format-button {
            transition: all 0.2s;
        }

        .format-button:hover {
            background-color: #edf2f7;
        }

        #mention-list {
            position: absolute;
            bottom: 100%;
            left: 0;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            max-height: 300px;
            overflow-y: auto;
            z-index: 50;
            width: 100%;
        }

        #mention-list div {
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        #mention-list div:hover {
            background-color: #f8fafc;
        }

        #mention-list img {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 9999px;
            margin-right: 0.5rem;
        }

        #mention-list .font-medium {
            font-weight: 500;
        }

        #mention-list .text-gray-500 {
            color: #6b7280;
            margin-left: 0.5rem;
        }

        #mention-list div[data-selected="true"] {
            background-color: #ebf4ff;
        }

        .message-input {
            min-height: 100px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            background: white;
            overflow-y: auto;
        }

        .mention {
            background-color: #E3F2FD;
            color: #1D9BD1;
            border-radius: 15px;
            padding: 0 6px 0 4px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            height: 20px;
            line-height: 20px;
            margin: 0 1px;
        }

        .mention:before {
            content: '@';
            margin-right: 1px;
        }

        .mention:hover {
            background-color: #D0E7FA;
            text-decoration: underline;
        }

        .message-input {
            min-height: 100px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            background: white;
            overflow-y: auto;
            outline: none;
        }

        .message-input[placeholder]:empty:before {
            content: attr(placeholder);
            color: #a0aec0;
            pointer-events: none;
            display: block;
        }

        .mention {
            background-color: #E3F2FD;
            color: #1D9BD1;
            border-radius: 15px;
            padding: 0 6px 0 4px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            height: 20px;
            line-height: 20px;
            margin: 0 1px;
        }

        .mention:before {
            content: '@';
            margin-right: 1px;
        }

        .mention:hover {
            background-color: #D0E7FA;
            text-decoration: underline;
        }


    </style>
@endpush
@section('content')

    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-md flex flex-col">
            <div class="p-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                <h1 class="text-xl font-bold flex items-center">
                    <i class="fab fa-slack mr-2"></i> {{ $teamName }}
                </h1>
            </div>

            <div class="flex-1 overflow-y-auto">
                <!-- Channels Section -->
                <div class="px-4 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Channels</h2>
                        <button onclick="toggleSection('channels')" class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-chevron-down" id="channels-arrow"></i>
                        </button>
                    </div>
                    <div id="channels-list" class="space-y-1">
                        @foreach ($initialData['channels'] as $channel)
                            <div class="flex items-center px-2 py-1.5 rounded hover:bg-gray-100 cursor-pointer"
                                 onclick="selectConversation('{{ $channel['id'] }}', '{{ $channel['name'] }}', 'channel')">
                                @if ($channel['is_private'])
                                    <i class="fas fa-lock text-gray-400 mr-2 text-xs"></i>
                                @else
                                    <i class="fas fa-hashtag text-gray-400 mr-2 text-sm"></i>
                                @endif
                                <span class="truncate">{{ $channel['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Users Section -->
                <div class="px-4 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Direct Messages</h2>
                        <button onclick="toggleSection('users')" class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-chevron-down" id="users-arrow"></i>
                        </button>
                    </div>
                    <div id="users-list" class="space-y-1">
                        @foreach ($initialData['users'] as $user)
                            <div class="flex items-center px-2 py-1.5 rounded hover:bg-gray-100 cursor-pointer"
                                 onclick="selectConversation('{{ $user['id'] }}', '{{ $user['name'] }}', 'user')">
                                @if ($user['avatar'])
                                    <img src="{{ $user['avatar'] }}" class="w-5 h-5 rounded-full mr-2">
                                @else
                                    <div class="w-5 h-5 rounded-full bg-gray-300 mr-2 flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($user['name'], 0, 1)) }}
                                    </div>
                                @endif
                                <span class="truncate">{{ $user['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 flex flex-col">
            <!-- Chat Header -->
            <div class="bg-white border-b border-gray-200 p-4 flex items-center">
                <div id="chat-header-placeholder" class="{{ session('current_conversation') ? 'hidden' : '' }}">
                    <h2 class="text-gray-500">Select a channel or user to start chatting</h2>
                </div>
                <div id="chat-header" class="{{ !session('current_conversation') ? 'hidden' : '' }} flex items-center">
                    <i id="conversation-icon" class="fas mr-2"></i>
                    <h2 id="conversation-name" class="font-medium"></h2>
                    <span id="conversation-type" class="ml-2 text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-500"></span>
                </div>
            </div>

            <!-- Messages Container -->
            <div id="messages-container" class="flex-1 overflow-y-auto p-4 bg-gray-50">
                <div class="flex items-center justify-center h-full text-gray-400">
                    <div class="text-center">
                        <i class="fas fa-comments text-4xl mb-2"></i>
                        <p>Select a conversation to view messages</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border-t border-gray-200 p-4">
                <div id="message-input-container" class="hidden">
                    <div class="flex space-x-2 mb-3">
                        <button onclick="formatText('bold')" class="format-button p-2 text-gray-500 hover:text-indigo-600 rounded-full">
                            <i class="fas fa-bold"></i>
                        </button>
                        <button onclick="formatText('italic')" class="format-button p-2 text-gray-500 hover:text-indigo-600 rounded-full">
                            <i class="fas fa-italic"></i>
                        </button>
                        <button onclick="formatText('code')" class="format-button p-2 text-gray-500 hover:text-indigo-600 rounded-full">
                            <i class="fas fa-code"></i>
                        </button>
                    </div>
                    <div class="flex space-x-3 items-end relative">
                        <div id="message-input" class="message-input flex-1" contenteditable="true" placeholder="Type your message..."></div>
                        <input type="hidden" id="message-raw" name="message">
                        <button onclick="sendMessage()" id="send-button"
                                class="bg-indigo-600 text-white p-3 rounded-lg hover:bg-indigo-700 shadow-md">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div id="success-toast" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg transform translate-y-16 transition-transform hidden">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="toast-message"></span>
        </div>
    </div>

    @include('app.slack.slackJs')

@endsection
