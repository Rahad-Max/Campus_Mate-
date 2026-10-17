<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\HelpdeskService;

class HelpdeskController extends Controller
{
    private HelpdeskService $helpdeskService;

    public function __construct()
    {
        $this->helpdeskService = new HelpdeskService();
    }

    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['helpdesk_session_id'])) {
            $_SESSION['helpdesk_session_id'] = 'sess_' . bin2hex(random_bytes(8));
        }

        $sessionId = $_SESSION['helpdesk_session_id'];
        $user = $this->authUser();

        // Retrieve past messages for this session
        $sql = "SELECT hm.*, hc.topic
                FROM `helpdesk_messages` hm
                JOIN `helpdesk_conversations` hc ON hc.id = hm.conversation_id
                WHERE hc.session_id = :sid
                ORDER BY hm.created_at ASC";
        $messages = \App\Core\Database::getInstance()->fetchAll($sql, [':sid' => $sessionId]);

        $this->render('helpdesk/index', [
            'title' => 'AI Academic Helpdesk — PUB CampusMate',
            'sessionId' => $sessionId,
            'messages' => $messages,
            'user' => $user
        ]);
    }

    public function ask(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $this->authUser();
        $userId = $user['id'] ?? 1; // Default to demo user if testing
        $sessionId = $_POST['session_id'] ?? ($_SESSION['helpdesk_session_id'] ?? 'sess_' . bin2hex(random_bytes(8)));
        $question = sanitize($_POST['question'] ?? '');

        if (empty($question)) {
            $this->json(['success' => false, 'message' => 'Please provide a question.'], 400);
            return;
        }

        try {
            $response = $this->helpdeskService->askQuestion((int)$userId, $sessionId, $question);
            $this->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Error querying AI helpdesk: ' . $e->getMessage()
            ], 500);
        }
    }
}
