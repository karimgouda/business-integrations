<script>

    let mentionUsers = [];
    let isMentionListVisible = false;

    document.addEventListener('DOMContentLoaded', function() {
        loadMentionUsers();
        initMentionSystem();
    });

    async function loadMentionUsers() {
        try {
            const response = await fetch('/slack/get-mention-users');
            if (!response.ok) throw new Error('Network response was not ok');

            const data = await response.json();
            mentionUsers = data.users || [];
            console.log('Loaded mention users:', mentionUsers);
            return mentionUsers;
        } catch (error) {
            console.error('Failed to load mention users:', error);
            return [];
        }
    }
    function initMentionSystem() {
        const editor = document.getElementById('message-input');

        editor.addEventListener('input', function(e) {
            const selection = window.getSelection();
            if (!selection.rangeCount) return;

            const range = selection.getRangeAt(0);
            const node = selection.anchorNode;
            const text = node.textContent || '';
            const offset = selection.anchorOffset;

            let atPos = -1;
            for (let i = offset - 1; i >= 0; i--) {
                if (text.charAt(i) === '@') {
                    atPos = i;
                    break;
                }
            }

            if (e.inputType === 'insertText' && e.data === '@') {
                showMentionList();
            }
            else if (atPos >= 0 && offset > atPos) {
                if (!isMentionListVisible) showMentionList();
                const searchTerm = text.substring(atPos + 1, offset).toLowerCase();
                filterMentionList(searchTerm);
            }
            else if (isMentionListVisible) {
                removeMentionList();
            }
        });

        editor.addEventListener('keydown', function(e) {
            if (!isMentionListVisible) return;

            if (e.key === 'Escape') {
                removeMentionList();
                e.preventDefault();
            }
            else if (['ArrowDown', 'ArrowUp', 'Enter'].includes(e.key)) {
                handleMentionNavigation(e);
                e.preventDefault();
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#mention-list') && e.target.id !== 'message-input') {
                removeMentionList();
            }
        });
    }

    function showMentionList() {
        if (mentionUsers.length === 0 || isMentionListVisible) return;

        const mentionList = document.createElement('div');
        mentionList.id = 'mention-list';
        mentionList.className = 'absolute bg-white shadow-lg rounded-md p-2 z-50 border border-gray-200 mt-1';
        mentionList.style.width = '250px';
        mentionList.style.maxHeight = '300px';
        mentionList.style.overflowY = 'auto';

        mentionUsers.forEach((user, index) => {
            const userElement = document.createElement('div');
            userElement.className = 'p-2 hover:bg-gray-100 cursor-pointer flex items-center';
            userElement.dataset.userId = user.slack_id;
            userElement.dataset.index = index;
            userElement.innerHTML = `
                <img src="${user.slack_avatar || 'https://via.placeholder.com/48'}"
                     class="w-6 h-6 rounded-full mr-2"
                     onerror="this.src='https://via.placeholder.com/48'">
                <span class="font-medium">${user.name}</span>
            `;
            userElement.addEventListener('click', () => {
                insertMention(user);
            });
            mentionList.appendChild(userElement);
        });

        const inputContainer = document.querySelector('#message-input-container .flex.space-x-3');
        inputContainer.appendChild(mentionList);

        const firstItem = mentionList.querySelector('div');
        if (firstItem) {
            firstItem.classList.add('bg-blue-50');
            firstItem.dataset.selected = 'true';
        }

        isMentionListVisible = true;
        console.log('Mention list shown');
    }

    function filterMentionList(searchTerm) {
        const mentionList = document.getElementById('mention-list');
        if (!mentionList) return;

        let hasMatches = false;
        const items = mentionList.children;

        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            const userName = item.querySelector('span.font-medium').textContent.toLowerCase();
            const slackName = item.querySelector('span.text-gray-500').textContent.substring(1).toLowerCase();

            if (userName.includes(searchTerm) || slackName.includes(searchTerm)) {
                item.style.display = 'flex';
                hasMatches = true;
            } else {
                item.style.display = 'none';
            }
        }

        if (!hasMatches) {
            removeMentionList();
        }
    }

    function handleMentionNavigation(e) {
        const mentionList = document.getElementById('mention-list');
        if (!mentionList) return;

        const visibleItems = Array.from(mentionList.children)
            .filter(item => item.style.display !== 'none');

        if (visibleItems.length === 0) return;

        const selectedIndex = visibleItems.findIndex(item => item.dataset.selected === 'true');
        let newIndex = 0;

        if (e.key === 'ArrowDown') {
            newIndex = (selectedIndex + 1) % visibleItems.length;
        }
        else if (e.key === 'ArrowUp') {
            newIndex = (selectedIndex - 1 + visibleItems.length) % visibleItems.length;
        }
        else if (e.key === 'Enter' && selectedIndex >= 0) {
            const userId = visibleItems[selectedIndex].dataset.userId;
            const user = mentionUsers.find(u => u.slack_id === userId);
            if (user) {
                insertMention(user);
                return;
            }
        }

        if (selectedIndex >= 0) {
            visibleItems[selectedIndex].classList.remove('bg-blue-50');
            visibleItems[selectedIndex].removeAttribute('data-selected');
        }

        visibleItems[newIndex].classList.add('bg-blue-50');
        visibleItems[newIndex].dataset.selected = 'true';
        visibleItems[newIndex].scrollIntoView({ block: 'nearest' });
    }

    let mentionsMap = {};

    function insertMention(user) {
        const editor = document.getElementById('message-input');
        const selection = window.getSelection();
        if (!selection.rangeCount) return;

        const range = selection.getRangeAt(0);
        const node = selection.anchorNode;
        const text = node.textContent || '';
        const offset = selection.anchorOffset;

        let atPos = -1;
        for (let i = offset - 1; i >= 0; i--) {
            if (text.charAt(i) === '@') {
                atPos = i;
                break;
            }
        }

        if (atPos >= 0) {
            range.setStart(node, atPos);
            range.setEnd(node, offset);
            range.deleteContents();
        }

        const mentionNode = document.createElement('span');
        mentionNode.className = 'mention';
        mentionNode.contentEditable = 'false';
        mentionNode.dataset.userId = user.slack_id;
        mentionNode.dataset.userName = user.name;
        mentionNode.textContent = `@${user.name}`;

        range.insertNode(mentionNode);

        const space = document.createTextNode(' ');
        range.insertNode(space);

        range.setStartAfter(space);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);

        removeMentionList();
    }
    function removeMentionList() {
        const mentionList = document.getElementById('mention-list');
        if (mentionList) {
            mentionList.remove();
            isMentionListVisible = false;
        }
    }


    let currentConversation = {
        id: null,
        name: null,
        type: null
    };

    @if(session('current_conversation'))
        currentConversation = @json(session('current_conversation'));
    selectConversation(currentConversation.id, currentConversation.name, currentConversation.type, false);
    @endif

    function toggleSection(section) {
        const list = document.getElementById(`${section}-list`);
        const arrow = document.getElementById(`${section}-arrow`);

        if (list.classList.contains('hidden')) {
            list.classList.remove('hidden');
            arrow.classList.remove('fa-chevron-up');
            arrow.classList.add('fa-chevron-down');
        } else {
            list.classList.add('hidden');
            arrow.classList.remove('fa-chevron-down');
            arrow.classList.add('fa-chevron-up');
        }
    }

    function selectConversation(id, name, type, updateHistory = true) {
        currentConversation = { id, name, type };

        document.getElementById('chat-header-placeholder').classList.add('hidden');
        document.getElementById('chat-header').classList.remove('hidden');
        document.getElementById('conversation-name').textContent = name;

        const icon = type === 'channel' ?
            (name.startsWith('priv') ? 'fa-lock' : 'fa-hashtag') : 'fa-user';
        document.getElementById('conversation-icon').className = `fas ${icon} mr-2`;

        document.getElementById('conversation-type').textContent =
            type === 'channel' ? 'Channel' : 'Direct Message';

        document.getElementById('message-input-container').classList.remove('hidden');
        document.getElementById('message-input').focus();

        loadMessages(id);

        if (updateHistory) {
            window.history.pushState(
                { conversation: currentConversation },
                '',
                `/slack/chat?conversation=${id}&type=${type}`
            );
        }
    }

    async function loadMessages(conversationId) {
        document.getElementById('messages-container').innerHTML = `
        <div class="flex justify-center items-center h-full">
            <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
        </div>
    `;

        try {
            if (mentionUsers.length === 0) {
                await loadMentionUsers();
            }

            const response = await fetch(`/slack/get-messages?conversation=${conversationId}&type=${currentConversation.type}`);
            const data = await response.json();

            if (data.ok) {
                renderMessages(data.messages);
            } else {
                showToast('Failed to load messages: ' + (data.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            showToast('Network error loading messages');
        }
    }
    function renderMessages(messages) {
        const container = document.getElementById('messages-container');
        container.innerHTML = '';

        if (messages.length === 0) {
            container.innerHTML = `
            <div class="flex items-center justify-center h-full text-gray-400">
                <div class="text-center">
                    <i class="far fa-comment-dots text-4xl mb-2"></i>
                    <p>No messages yet</p>
                    <p class="text-sm mt-1">Start the conversation</p>
                </div>
            </div>
        `;
            return;
        }

        messages.reverse().forEach(msg => {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message';

            const timestamp = new Date(msg.ts * 1000);
            const formattedTime = timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const formattedDate = timestamp.toLocaleDateString();

            let processedText = msg.text;

            processedText = processedText.replace(/<@([^>|]+)(?:\|([^>]+))?>/g, (match, userId, userName) => {
                if (userName) {
                    return `@${userName}`;
                }

                const user = mentionUsers.find(u => u.slack_id === userId);
                return user ? `@${user.name}` : match;
            });

            messageDiv.innerHTML = `
            <div class="message-header">
                <div class="flex items-center">
                    ${msg.user.avatar ?
                `<img src="${msg.user.avatar}" class="message-avatar">` :
                `<div class="message-avatar-placeholder">${msg.user.name.charAt(0).toUpperCase()}</div>`
            }
                    <span class="message-sender">${msg.user.name}</span>
                </div>
                <span class="message-time" title="${formattedDate}">${formattedTime}</span>
            </div>
            <div class="message-content">${processedText}</div>
        `;

            container.appendChild(messageDiv);
        });

        container.scrollTop = container.scrollHeight;
    }
    function formatText(type) {
        const editor = document.getElementById('message-input');
        const selection = window.getSelection();

        if (selection.rangeCount === 0) return;

        const range = selection.getRangeAt(0);
        const selectedText = range.toString();

        let formattedText = '';
        switch(type) {
            case 'bold': formattedText = `*${selectedText}*`; break;
            case 'italic': formattedText = `_${selectedText}_`; break;
            case 'code': formattedText = `\`${selectedText}\``; break;
        }

        range.deleteContents();
        range.insertNode(document.createTextNode(formattedText));

        const newRange = document.createRange();
        newRange.setStart(range.startContainer, range.startOffset);
        newRange.setEnd(range.endContainer, range.endOffset + formattedText.length - selectedText.length);
        selection.removeAllRanges();
        selection.addRange(newRange);

        editor.focus();
    }

    async function sendMessage() {
        const message = prepareMessageForSlack();

        if (!message || !message.trim() || message === '@' || !currentConversation.id) {
            showToast('Message cannot be empty');
            return;
        }

        const sendBtn = document.getElementById('send-button');
        const originalContent = sendBtn.innerHTML;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        sendBtn.disabled = true;

        try {
            const response = await fetch('/slack/send-message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    conversation: currentConversation.id,
                    text: document.getElementById('message-raw').value,
                    type: currentConversation.type
                })
            });

            const result = await response.json();

            if (result.ok) {
                document.getElementById('message-input').innerHTML = '';
                loadMessages(currentConversation.id);
                showToast(`Message sent to ${currentConversation.name}`);
            } else {
                showToast('Failed to send: ' + (result.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error sending message:', error);
            showToast('Network error sending message');
        } finally {
            sendBtn.innerHTML = originalContent;
            sendBtn.disabled = false;
        }
    }

    function prepareMessageForSlack() {
        const editor = document.getElementById('message-input');
        const hiddenInput = document.getElementById('message-raw');

        const clone = document.createElement('div');
        clone.innerHTML = editor.innerHTML;

        const mentions = clone.querySelectorAll('.mention');
        mentions.forEach(mention => {
            const userId = mention.dataset.userId;
            const userName = mention.dataset.userName;
            const replacement = document.createTextNode(`<@${userId}|${userName}>`);
            mention.replaceWith(replacement);
        });

        const processedMessage = clone.textContent || clone.innerText;
        hiddenInput.value = processedMessage;

        return processedMessage;
    }
    function showToast(message) {
        const toast = document.getElementById('success-toast');
        document.getElementById('toast-message').textContent = message;

        toast.classList.remove('hidden');
        toast.classList.remove('translate-y-16');
        toast.classList.add('translate-y-0');

        setTimeout(() => {
            toast.classList.remove('translate-y-0');
            toast.classList.add('translate-y-16');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 3000);
    }

    document.getElementById('message-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    window.addEventListener('popstate', function(event) {
        if (event.state && event.state.conversation) {
            selectConversation(
                event.state.conversation.id,
                event.state.conversation.name,
                event.state.conversation.type,
                false
            );
        }
    });
</script>
