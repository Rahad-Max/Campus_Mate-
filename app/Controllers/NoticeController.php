<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notice;

class NoticeController extends Controller
{
    private Notice $noticeModel;

    public function __construct()
    {
        $this->noticeModel = new Notice();
    }

    public function index(): void
    {
        $user = $this->authUser();
        $role = $user['role'] ?? 'all';
        $notices = $this->noticeModel->getActiveNotices($role);

        $this->render('notices/index', [
            'title' => 'Campus Notices & Announcements — PUB CampusMate',
            'notices' => $notices
        ]);
    }

    public function show(string $id): void
    {
        $notice = $this->noticeModel->find((int)$id);
        if (!$notice) {
            $this->redirect('/notices');
            return;
        }

        $this->render('notices/show', [
            'title' => "Notice: {$notice['title']} — PUB CampusMate",
            'notice' => $notice
        ]);
    }
}
