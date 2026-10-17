/**
 * PUB CampusMate — AI Academic Helpdesk JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    const chatForm = document.getElementById('helpdeskForm');
    const inputQuestion = document.getElementById('helpdeskQuestion');
    const chatContainer = document.getElementById('chatMessagesContainer');
    const promptChips = document.querySelectorAll('.prompt-suggestion-chip');

    function scrollToBottom() {
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }

    scrollToBottom();

    // Suggestion chips
    promptChips.forEach(chip => {
        chip.addEventListener('click', () => {
            if (inputQuestion) {
                inputQuestion.value = chip.dataset.prompt || chip.textContent.trim();
                inputQuestion.focus();
            }
        });
    });

    if (chatForm) {
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const question = inputQuestion.value.trim();
            if (!question) return;

            const sessionId = document.getElementById('helpdeskSessionId')?.value || '';

            // Append user message immediately
            appendUserMessage(question);
            inputQuestion.value = '';
            scrollToBottom();

            // Append thinking indicator
            const thinkingId = appendThinkingIndicator();
            scrollToBottom();

            try {
                const formData = new FormData();
                formData.append('question', question);
                formData.append('session_id', sessionId);
                formData.append('_csrf_token', window.csrfToken);

                const response = await fetch('/helpdesk/ask', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();
                removeThinkingIndicator(thinkingId);

                if (data.success && data.data) {
                    appendAssistantMessage(data.data.answer, data.data.citations);
                } else {
                    appendAssistantMessage(data.message || 'Sorry, an error occurred while retrieving official documentation.', []);
                }
            } catch (err) {
                removeThinkingIndicator(thinkingId);
                appendAssistantMessage('Network error communicating with AI helpdesk backend.', []);
            }

            scrollToBottom();
        });
    }

    function appendUserMessage(text) {
        const div = document.createElement('div');
        div.className = 'd-flex justify-content-end mb-3';
        div.innerHTML = `
            <div class="chat-bubble user-bubble bg-pub-primary text-white p-3 rounded-4" style="max-width: 80%;">
                <div class="small fw-semibold mb-1 text-light">You</div>
                <div>${escapeHtml(text)}</div>
            </div>
        `;
        chatContainer.appendChild(div);
    }

    function appendAssistantMessage(text, citations) {
        const div = document.createElement('div');
        div.className = 'd-flex justify-content-start mb-3';

        let citationsHtml = '';
        if (citations && citations.length > 0) {
            citationsHtml = '<div class="mt-3 pt-2 border-top border-light-subtle"><div class="small fw-bold text-muted mb-1"><i class="bi bi-journal-bookmark-fill me-1"></i>Official Citations:</div><div class="d-flex flex-wrap gap-1">';
            citations.forEach(c => {
                citationsHtml += `<span class="badge bg-light text-dark border"><i class="bi bi-file-earmark-pdf me-1 text-danger"></i>${escapeHtml(c.chunk_title || c.document_title)}</span>`;
            });
            citationsHtml += '</div></div>';
        }

        div.innerHTML = `
            <div class="chat-bubble assistant-bubble bg-white border p-3 rounded-4 shadow-sm" style="max-width: 80%;">
                <div class="small fw-bold text-pub-primary mb-1"><i class="bi bi-robot me-1"></i>CampusMate AI Advisor</div>
                <div style="white-space: pre-line;">${escapeHtml(text)}</div>
                ${citationsHtml}
            </div>
        `;
        chatContainer.appendChild(div);
    }

    function appendThinkingIndicator() {
        const id = 'thinking_' + Date.now();
        const div = document.createElement('div');
        div.id = id;
        div.className = 'd-flex justify-content-start mb-3';
        div.innerHTML = `
            <div class="chat-bubble bg-light border p-3 rounded-4 text-muted small">
                <i class="bi bi-hourglass-split me-1 spin"></i> Retrieving university ordinances and verifying policy...
            </div>
        `;
        chatContainer.appendChild(div);
        return id;
    }

    function removeThinkingIndicator(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function escapeHtml(string) {
        const div = document.createElement('div');
        div.innerText = string;
        return div.innerHTML;
    }
});
