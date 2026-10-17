<?php
namespace App\Services;

use App\Core\Database;
use App\Models\AcademicDocument;

class HelpdeskService
{
    private AcademicDocument $docModel;
    private Database $db;

    public function __construct()
    {
        $this->docModel = new AcademicDocument();
        $this->db = Database::getInstance();
    }

    /**
     * Retrieve relevant document chunks and generate a grounded answer with citations.
     */
    public function askQuestion(int $userId, string $sessionId, string $question): array
    {
        // 1. Get or create conversation session
        $conv = $this->db->fetch(
            "SELECT * FROM `helpdesk_conversations` WHERE `session_id` = :sid LIMIT 1",
            [':sid' => $sessionId]
        );

        if (!$conv) {
            $convId = $this->db->query(
                "INSERT INTO `helpdesk_conversations` (`user_id`, `session_id`, `topic`, `status`) VALUES (:uid, :sid, :topic, 'active')",
                [':uid' => $userId, ':sid' => $sessionId, ':topic' => mb_substr($question, 0, 50)]
            );
            $convId = (int)$this->db->lastInsertId();
        } else {
            $convId = (int)$conv['id'];
        }

        // 2. Save user message
        $this->db->query(
            "INSERT INTO `helpdesk_messages` (`conversation_id`, `sender_type`, `message`) VALUES (:cid, 'user', :msg)",
            [':cid' => $convId, ':msg' => $question]
        );

        // 3. Retrieve relevant chunks (RAG retrieval)
        $chunks = $this->docModel->searchChunks($question, 4);

        $citations = [];
        $contextTexts = [];

        foreach ($chunks as $chunk) {
            $citations[] = [
                'document_title' => $chunk['document_title'],
                'document_code' => $chunk['document_code'],
                'chunk_title' => $chunk['chunk_title'],
                'relevance' => round((float)($chunk['relevance'] ?? 1.0), 2)
            ];
            $contextTexts[] = "• " . $chunk['chunk_title'] . ": " . $chunk['content'];
        }

        // 4. Grounded response formulation
        if (empty($chunks)) {
            $answer = "I could not find a specific section in the official PUB Academic Ordinances matching your query. Please consult your Academic Advisor or Department Coordinator for authoritative guidance.";
            $confidence = 0.30;
        } else {
            $answer = "According to official university documents:\n\n" . implode("\n\n", $contextTexts);
            $confidence = 0.95;
        }

        // 5. Save assistant reply with citations
        $this->db->query(
            "INSERT INTO `helpdesk_messages` (`conversation_id`, `sender_type`, `message`, `sources_cited_json`, `confidence_score`)
             VALUES (:cid, 'assistant', :msg, :sources, :conf)",
            [
                ':cid' => $convId,
                ':msg' => $answer,
                ':sources' => json_encode($citations, JSON_UNESCAPED_UNICODE),
                ':conf' => $confidence
            ]
        );

        return [
            'question' => $question,
            'answer' => $answer,
            'citations' => $citations,
            'confidence' => $confidence,
            'session_id' => $sessionId
        ];
    }
}
