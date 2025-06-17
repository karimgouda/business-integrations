<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Business Integrations Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body class="bg-gray-50">
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-indigo-600">Business Integrations</h1>
            <nav class="flex items-center space-x-4">
                <a href="#" class="text-gray-600 hover:text-indigo-600 px-3 py-2">Home</a>
                <a href="#" class="text-gray-600 hover:text-indigo-600 px-3 py-2">Documentation</a>

                @auth
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-indigo-600 px-3 py-2 bg-transparent border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('loginPage') }}" class="text-gray-600 hover:text-indigo-600 px-3 py-2 bg-transparent border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                        Login
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="p-6 sm:p-10">
                    <div class="text-center mb-10">
                        <h2 class="text-3xl font-extrabold text-gray-900">Business Integrations</h2>
                        <p class="mt-2 text-lg text-gray-600">Connect your favorite business apps to streamline workflows</p>
                    </div>

                    <!-- Integration Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Slack Integration -->
                        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-center mb-4">
                                <img src="https://cdn.worldvectorlogo.com/logos/slack-new-logo.svg" alt="Slack" class="h-10 w-10">
                                <h3 class="ml-3 text-lg font-medium text-gray-900">Slack</h3>
                            </div>
                            <p class="text-gray-600 mb-4">Connect with Slack to receive notifications and manage workflows directly from your channels.</p>
                            @if(auth()->user()->account?->client_id !== null && auth()->user()->account?->slack_access_token !== null)
                                <a href="{{route('slackChat',['account_id'=>auth()->user()->account_id])}}" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition-colors flex items-center justify-center">
                                    Join Slack
                                </a>
                            @else
                                <button onclick="connectSlack()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                                    Connect Slack
                                </button>
                            @endif
                        </div>

                        <!-- Messenger Integration -->
                        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-center mb-4">
                                <img src="{{asset('messenger.png')}}" alt="Messenger" class="h-10 w-10">
                                <h3 class="ml-3 text-lg font-medium text-gray-900">Facebook Messenger</h3>
                            </div>
                            <p class="text-gray-600 mb-4">Connect with Messenger to manage conversations, view messages, and send replies to your customers.</p>
                            @if(auth()->user()->account?->messenger_page_id !== null && auth()->user()->account?->messenger_access_token !== null)
                                <a href="{{route('messengerChat')}}" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition-colors flex items-center justify-center">
                                    Open Messenger
                                </a>
                            @else
                                <button onclick="connectMessenger()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                                    Connect Messenger
                                </button>
                            @endif
                        </div>

                        <!-- Google Workspace -->
                        <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                            <div class="flex items-center mb-4">
                                <div class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <h3 class="ml-3 text-lg font-medium text-gray-900">Google Workspace</h3>
                            </div>
                            <p class="text-gray-500 mb-4">Coming soon - Integrate with Google Calendar, Drive and more.</p>
                            <button disabled class="w-full bg-gray-300 text-gray-600 font-medium py-2 px-4 rounded-md cursor-not-allowed">
                                Coming Soon
                            </button>
                        </div>
                    </div>

                    <!-- Connected Apps Section -->
                    <div class="mt-12">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Your Connected Apps</h3>
                        <div class="bg-gray-50 rounded-lg p-6">
                            @if(auth()->user()->account?->client_id !== null && auth()->user()->account?->slack_access_token !== null)
                                <div class="flex items-center space-x-4 mb-4">
                                    <img src="https://cdn.worldvectorlogo.com/logos/slack-new-logo.svg" alt="Slack" class="h-10 w-10">
                                    <span class="font-medium">Slack</span>
                                    <span class="ml-auto text-sm text-green-600 font-medium">Connected</span>
                                </div>
                            @endif
                            @if(auth()->user()->account?->messenger_page_id !== null && auth()->user()->account?->messenger_access_token !== null)
                                <div class="flex items-center space-x-4">
                                    <img src="{{asset('messenger.png')}}" alt="Slack" class="h-10 w-10">
                                    <span class="font-medium">Facebook Messenger</span>
                                    <span class="ml-auto text-sm text-green-600 font-medium">Connected</span>
                                </div>
                            @endif
                            @if((auth()->user()->account?->client_id === null || auth()->user()->account?->slack_access_token === null) &&
                                (auth()->user()->account?->messenger_page_id === null || auth()->user()->account?->messenger_access_token === null))
                                <p class="text-gray-600">No apps connected yet</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">© 2025 Business Integrations Platform. All rights reserved.</p>
        </div>
    </footer>
</div>

<!-- Slack OAuth Modal -->
<div id="slackModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">Connect Slack</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <p class="text-gray-600 mb-6">Click the button below to authorize our app to access your Slack workspace.</p>
        @if(auth()->user()->account?->client_id !== null)
            <a href="https://slack.com/oauth/v2/authorize?client_id={{auth()->user()->account?->client_id}}&scope=incoming-webhook,commands,users:read&user_scope="
               class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center font-medium py-2 px-4 rounded-md transition-colors">
                <i class="fab fa-slack mr-2"></i> Authorize Slack
            </a>
        @else
            <a href="https://api.slack.com/apps" target="_blank"
               class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center font-medium py-2 px-4 rounded-md transition-colors">
                <i class="fab fa-slack mr-2"></i> Authorize Slack
            </a>
        @endif
    </div>
</div>

<!-- Messenger OAuth Modal -->
<div id="messengerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">Connect Messenger</h3>
            <button onclick="closeMessengerModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <p class="text-gray-600 mb-6">Click the button below to authorize our app to access your Facebook Page messages.</p>
        <a href=""
           class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-medium py-2 px-4 rounded-md transition-colors">
            <i class="fab fa-facebook-messenger mr-2"></i> Authorize Messenger
        </a>
    </div>
</div>

<script>
    function connectSlack() {
        document.getElementById('slackModal').classList.remove('hidden');
    }

    function connectMessenger() {
        document.getElementById('messengerModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('slackModal').classList.add('hidden');
    }

    function closeMessengerModal() {
        document.getElementById('messengerModal').classList.add('hidden');
    }

    // Check for OAuth callbacks
    if (window.location.search.includes('slack_auth_success')) {
        Toastify({
            text: "Slack connected successfully!",
            duration: 3000,
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: "#4CAF50",
        }).showToast();
        setTimeout(() => window.location.href = "{{route('integration')}}", 1500);
    }

    if (window.location.search.includes('messenger_auth_success')) {
        Toastify({
            text: "Messenger connected successfully!",
            duration: 3000,
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: "#4CAF50",
        }).showToast();
        setTimeout(() => window.location.href = "{{route('integration')}}", 1500);
    }
</script>

</body>
</html>
