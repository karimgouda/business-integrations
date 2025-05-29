@extends('layouts.master')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fab fa-slack mr-2"></i> Slack Channels - {{ $teamName }}
                </h1>
            </div>

            <div class="p-6">
                @if (session('error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg flex items-start">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-1"></i>
                        <div>
                            <p class="font-medium text-red-800">Error</p>
                            <p class="text-red-600">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($channels as $channel)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow transform hover:-translate-y-1">
                            <div class="flex items-center mb-2">
                                @if ($channel['is_private'])
                                    <i class="fas fa-lock text-gray-500 mr-2"></i>
                                @else
                                    <i class="fas fa-hashtag text-gray-500 mr-2"></i>
                                @endif
                                <h3 class="font-medium">{{ $channel['name'] }}</h3>
                            </div>
                            <div class="text-sm text-gray-600 mb-3 flex items-center">
                                <i class="fas fa-users mr-1"></i> {{ $channel['member_count'] }} members
                            </div>
                            <button onclick="openChat('{{ $channel['id'] }}', '{{ $channel['name'] }}')"
                                    class="w-full bg-indigo-100 text-indigo-700 hover:bg-indigo-200 px-3 py-2 rounded-md text-sm font-medium transition-colors flex items-center justify-center">
                                <i class="fas fa-paper-plane mr-2"></i> Send Message
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-lg w-full max-w-md mx-4 shadow-xl transform transition-all">
            <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-t-lg">
                <h3 class="text-lg font-medium flex items-center">
                    <i class="fab fa-slack mr-2"></i>
                    <span id="modalChannelName"></span>
                </h3>
                <button onclick="closeModal()" class="float-right text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-4">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2 font-medium">Message Preview:</label>
                    <div id="messagePreview" class="bg-gray-50 p-3 rounded border border-gray-200 min-h-12"></div>
                </div>

                <div class="flex space-x-2 mb-4">
                    <button onclick="formatText('bold')" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200" title="Bold">
                        <i class="fas fa-bold"></i>
                    </button>
                    <button onclick="formatText('italic')" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200" title="Italic">
                        <i class="fas fa-italic"></i>
                    </button>
                    <button onclick="formatText('code')" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200" title="Code">
                        <i class="fas fa-code"></i>
                    </button>
                </div>

                <textarea id="messageText" class="w-full border border-gray-300 rounded-lg p-3 mb-4 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                          rows="4" placeholder="Type your message here..."></textarea>

                <div class="flex justify-end space-x-3">
                    <button onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button onclick="sendMessage()" id="sendButton" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                        <i class="fas fa-paper-plane mr-2"></i> Send
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Toast Notification -->
    <div id="successToast" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-16 transition-transform duration-300 hidden z-50">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="toastMessage">Message sent successfully!</span>
            <button onclick="hideToast()" class="ml-4 text-white hover:text-green-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <script>
        let currentChannelId = null;
        let currentChannelName = null;

        function openChat(channelId, channelName) {
            currentChannelId = channelId;
            currentChannelName = channelName;

            document.getElementById('modalChannelName').textContent = `Message to #${channelName}`;
            document.getElementById('messageModal').classList.remove('hidden');
            document.getElementById('messageText').focus();
        }

        function closeModal() {
            document.getElementById('messageModal').classList.add('hidden');
            document.getElementById('messageText').value = '';
            document.getElementById('messagePreview').textContent = '';
        }

        function formatText(type) {
            const textarea = document.getElementById('messageText');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);

            let formattedText = '';
            switch(type) {
                case 'bold': formattedText = `*${selectedText}*`; break;
                case 'italic': formattedText = `_${selectedText}_`; break;
                case 'code': formattedText = "```" + selectedText + "```"; break;
            }

            textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
            updatePreview();
        }

        function updatePreview() {
            const message = document.getElementById('messageText').value;
            document.getElementById('messagePreview').textContent = message;
        }

        document.getElementById('messageText').addEventListener('input', updatePreview);

        function showToast(message) {
            document.getElementById('toastMessage').textContent = message;
            const toast = document.getElementById('successToast');
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.remove('translate-y-16');
                toast.classList.add('translate-y-0');
            }, 10);

            setTimeout(hideToast, 5000);
        }

        function hideToast() {
            const toast = document.getElementById('successToast');
            toast.classList.add('translate-y-16');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }

        async function sendMessage() {
            const message = document.getElementById('messageText').value.trim();
            if (!message) return;

            const sendBtn = document.getElementById('sendButton');
            const originalContent = sendBtn.innerHTML;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';
            sendBtn.disabled = true;

            try {
                const response = await fetch('/slack/send-message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        channel: currentChannelId,
                        text: message
                    })
                });

                const result = await response.json();

                if (result.ok) {
                    showToast('Message sent successfully to #' + currentChannelName);
                    closeModal();
                } else {
                    showToast('Error: ' + (result.error || 'Failed to send message'));
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Network error: Please try again');
            } finally {
                sendBtn.innerHTML = originalContent;
                sendBtn.disabled = false;
            }
        }

        // Close modal when clicking outside
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

    <style>
        #messageModal {
            backdrop-filter: blur(5px);
        }
        #messageText {
            min-height: 120px;
            transition: border-color 0.3s;
        }
        #messagePreview {
            white-space: pre-wrap;
            word-break: break-word;
        }
        #successToast {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
    </style>
@endsection
