<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Teacher;

class FacultyController extends Controller
{
    private Teacher $teacherModel;

    public function __construct()
    {
        $this->teacherModel = new Teacher();
    }

    public function index(): void
    {
        $sql = "SELECT t.*, u.first_name, u.last_name, u.email, u.phone, u.avatar_url,
                       d.name as department_name, d.code as department_code,
                       fp.bio, fp.research_interests, fp.education, fp.office_hours, fp.personal_website
                FROM `teachers` t
                JOIN `users` u ON u.id = t.user_id
                JOIN `departments` d ON d.id = t.department_id
                LEFT JOIN `faculty_profiles` fp ON fp.teacher_id = t.id
                WHERE t.status = 'active'
                ORDER BY FIELD(t.designation, 'Professor & Head', 'Professor', 'Associate Professor', 'Assistant Professor', 'Lecturer'), u.first_name ASC";

        $faculty = \App\Core\Database::getInstance()->fetchAll($sql);

        $this->render('faculty/index', [
            'title' => 'Faculty Directory — PUB CampusMate',
            'faculty' => $faculty
        ]);
    }
}
