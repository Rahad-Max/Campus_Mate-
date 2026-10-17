<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ClassSchedule;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;

class TimetableController extends Controller
{
    private ClassSchedule $scheduleModel;
    private Semester $semesterModel;

    public function __construct()
    {
        $this->scheduleModel = new ClassSchedule();
        $this->semesterModel = new Semester();
    }

    public function index(): void
    {
        $user = $this->authUser();
        $role = $user['role'] ?? 'guest';
        $semester = $this->semesterModel->getCurrent();
        $semId = (int)($semester['id'] ?? 2);

        $timetable = [];

        if ($role === 'student') {
            $studentModel = new Student();
            $s = $studentModel->findByUserId($user['id']);
            $timetable = $s ? $this->scheduleModel->getStudentTimetable((int)$s['id'], $semId) : [];
        } elseif (in_array($role, ['teacher', 'academic_advisor'], true)) {
            $teacherModel = new Teacher();
            $t = $teacherModel->findByUserId($user['id']);
            $timetable = $t ? $this->scheduleModel->getTeacherTimetable((int)$t['id'], $semId) : [];
        } else {
            // Coordinator view: all schedules
            $sql = "SELECT cs.*, r.room_number, r.building,
                           s.section_name, c.course_code, c.title as course_title,
                           u.first_name as teacher_first_name, u.last_name as teacher_last_name
                    FROM `class_schedules` cs
                    JOIN `sections` s ON s.id = cs.section_id
                    JOIN `course_offerings` co ON co.id = s.course_offering_id
                    JOIN `courses` c ON c.id = co.course_id
                    JOIN `rooms` r ON r.id = cs.room_id
                    JOIN `teachers` t ON t.id = cs.teacher_id
                    JOIN `users` u ON u.id = t.user_id
                    WHERE cs.semester_id = :sid
                    ORDER BY FIELD(cs.day_of_week, 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), cs.start_time ASC";
            $timetable = \App\Core\Database::getInstance()->fetchAll($sql, [':sid' => $semId]);
        }

        $this->render('timetable/index', [
            'title' => 'Weekly Timetable & Schedule — PUB CampusMate',
            'semester' => $semester,
            'timetable' => $timetable,
            'role' => $role
        ]);
    }
}
