<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Business Integrations Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-50">
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-indigo-600">Business Integrations</h1>
            <nav>
                <a href="#" class="text-gray-600 hover:text-indigo-600 px-3 py-2">Home</a>
                <a href="#" class="text-gray-600 hover:text-indigo-600 px-3 py-2">Documentation</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="p-6 sm:p-10">
                    <div class="text-center mb-10">
                        <h2 class="text-3xl font-extrabold text-gray-900">Connect Your Business Tools</h2>
                        <p class="mt-2 text-lg text-gray-600">Integrate with your favorite business apps to streamline workflows</p>
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
                            <button onclick="connectSlack()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                                Connect Slack
                            </button>
                        </div>

                        <!-- Coming Soon Integrations -->
                        <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                            <div class="flex items-center mb-4">
                                <div class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                    <i class="fas fa-plus text-gray-500"></i>
                                </div>
                                <h3 class="ml-3 text-lg font-medium text-gray-900">Microsoft Teams</h3>
                            </div>
                            <p class="text-gray-500 mb-4">Coming soon - Microsoft Teams integration for seamless collaboration.</p>
                            <button disabled class="w-full bg-gray-300 text-gray-600 font-medium py-2 px-4 rounded-md cursor-not-allowed">
                                Coming Soon
                            </button>
                        </div>

                        <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                            <div class="flex items-center mb-4">
                                <div class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                    <i class="fas fa-plus text-gray-500"></i>
                                </div>
                                <h3 class="ml-3 text-lg font-medium text-gray-900">Google Workspace</h3>
                            </div>
                            <p class="text-gray-500 mb-4">Coming soon - Integrate with Google Calendar, Drive and more.</p>
                            <button disabled class="w-full bg-gray-300 text-gray-600 font-medium py-2 px-4 rounded-md cursor-not-allowed">
                                Coming Soon
                            </button>
                        </div>
                    </div>

                    <!-- Already Connected Section -->
                    <div class="mt-12">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Your Connected Apps</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-600" id="connectedAppsText">No apps connected yet</p>
                            <div id="connectedAppsList" class="hidden mt-4">
                                <!-- Will be populated by JavaScript -->
                            </div>
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
        <a href="https://slack.com/oauth/v2/authorize?client_id=8950173032371.8958342531346&scope=incoming-webhook,commands,users:read&user_scope="
           class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center font-medium py-2 px-4 rounded-md transition-colors">
            <i class="fab fa-slack mr-2"></i> Authorize Slack
        </a>
    </div>
</div>

<script>
    function connectSlack() {
        document.getElementById('slackModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('slackModal').classList.add('hidden');
    }

    // Check for OAuth callback
    if (window.location.search.includes('slack_auth_success')) {
        document.getElementById('connectedAppsText').classList.add('hidden');
        document.getElementById('connectedAppsList').classList.remove('hidden');
        document.getElementById('connectedAppsList').innerHTML = `
                <div class="flex items-center p-3 bg-white rounded border border-gray-200">
                    <img src="https://cdn.worldvectorlogo.com/logos/slack-new-logo.svg" alt="Slack" class="h-8 w-8">
                    <div class="ml-3">
                        <h4 class="font-medium">Slack</h4>
                        <p class="text-sm text-gray-500">Connected to ${new URLSearchParams(window.location.search).get('team_name')}</p>
                    </div>
                    <button class="ml-auto text-red-500 hover:text-red-700">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            `;
    }
</script>
</body>
</html>
