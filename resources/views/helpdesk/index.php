<div class="row align-items-center mb-3">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1"><i class="bi bi-stars text-warning me-2"></i>AI Academic Helpdesk</h2>
        <p class="text-muted mb-0">Ask questions about university academic ordinances, credit rules, prerequisites, and registration policies.</p>
    </div>
    <div class="col-md-4 text-md-end mt-2 mt-md-0">
        <span class="badge bg-light text-primary border px-3 py-2">
            <i class="bi bi-shield-check text-success me-1"></i> RAG Grounded on Official Ordinances
        </span>
    </div>
</div>

<div class="card card-pub shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success p-2 rounded-circle"><i class="bi bi-robot"></i></span>
            <div>
                <h6 class="mb-0 fw-bold">CampusMate Academic AI Assistant</h6>
                <small class="text-muted">Citing official Pundra University Ordinances & Syllabus Catalogs</small>
            </div>
        </div>
        <span class="badge bg-light text-muted border font-monospace small">Session: <?= substr($sessionId, 0, 14) ?></span>
    </div>

    <!-- Chat Messages Scroll Container -->
    <div class="card-body p-4 bg-light" id="chatMessagesContainer" style="height: 480px; overflow-y: auto;">
        <!-- Welcome Prompt -->
        <div class="d-flex justify-content-start mb-3">
            <div class="chat-bubble bg-white border p-3 rounded-4 shadow-sm" style="max-width: 80%;">
                <div class="small fw-bold text-pub-primary mb-1"><i class="bi bi-robot me-1"></i>CampusMate AI Advisor</div>
                <div>Hello! I am your AI Academic Companion for PUB. I can answer questions about course prerequisite rules, semester credit minimums/maximums, examination policies, and add/drop procedures directly citing university documentation. How can I help you today?</div>
            </div>
        </div>

        <?php foreach ($messages as $msg): ?>
            <?php if ($msg['sender_type'] === 'user'): ?>
                <div class="d-flex justify-content-end mb-3">
                    <div class="chat-bubble user-bubble bg-pub-primary text-white p-3 rounded-4" style="max-width: 80%;">
                        <div class="small fw-semibold mb-1 text-light">You</div>
                        <div><?= e($msg['message']) ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-start mb-3">
                    <div class="chat-bubble assistant-bubble bg-white border p-3 rounded-4 shadow-sm" style="max-width: 80%;">
                        <div class="small fw-bold text-pub-primary mb-1"><i class="bi bi-robot me-1"></i>CampusMate AI Advisor</div>
                        <div style="white-space: pre-line;"><?= e($msg['message']) ?></div>
                        
                        <?php 
                        $citations = !empty($msg['sources_cited_json']) ? json_decode($msg['sources_cited_json'], true) : [];
                        if (!empty($citations)):
                        ?>
                            <div class="mt-3 pt-2 border-top border-light-subtle">
                                <div class="small fw-bold text-muted mb-1"><i class="bi bi-journal-bookmark-fill me-1"></i>Official Citations:</div>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach ($citations as $cit): ?>
                                        <span class="badge bg-light text-dark border"><i class="bi bi-file-earmark-pdf me-1 text-danger"></i><?= e($cit['chunk_title'] ?? ($cit['document_title'] ?? 'Official Doc')) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Suggested Query Chips -->
    <div class="p-3 bg-white border-top border-bottom">
        <div class="small fw-bold text-muted mb-2"><i class="bi bi-lightbulb me-1"></i>Suggested Questions:</div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary prompt-suggestion-chip" data-prompt="What is the maximum number of credits I can take this semester if I am on academic probation?">
                Probation credit limits?
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary prompt-suggestion-chip" data-prompt="What are the prerequisite requirements for Data Structures and Algorithms?">
                Course prerequisites?
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary prompt-suggestion-chip" data-prompt="What is the deadline and rule for dropping a registered course?">
                Add/Drop procedure?
            </button>
        </div>
    </div>

    <!-- Chat Input Form -->
    <div class="card-footer bg-white p-3">
        <form id="helpdeskForm" class="d-flex gap-2">
            <input type="hidden" id="helpdeskSessionId" value="<?= e($sessionId) ?>">
            <input type="text" id="helpdeskQuestion" class="form-control" placeholder="Ask about academic ordinances, credit policies, prerequisites..." required autocomplete="off">
            <button type="submit" class="btn btn-pub px-4">
                <i class="bi bi-send-fill me-1"></i> Ask
            </button>
        </form>
    </div>
</div>
