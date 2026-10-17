<?php
namespace App\Services;

use App\Models\Course;
use App\Models\Student;

class PrerequisiteService
{
    private Course $courseModel;
    private Student $studentModel;

    public function __construct()
    {
        $this->courseModel = new Course();
        $this->studentModel = new Student();
    }

    /**
     * Check if a student meets all prerequisites for a specific course.
     * Returns ['passed' => bool, 'missing' => array]
     */
    public function check(int $studentId, int $courseId): array
    {
        $prereqs = $this->courseModel->getPrerequisites($courseId);
        if (empty($prereqs)) {
            return ['passed' => true, 'missing' => []];
        }

        $passedCourseIds = $this->studentModel->getPassedCourseIds($studentId);
        $missing = [];

        foreach ($prereqs as $p) {
            $prereqCourseId = (int)$p['prerequisite_course_id'];
            if (!in_array($prereqCourseId, $passedCourseIds, true)) {
                $missing[] = [
                    'course_id' => $prereqCourseId,
                    'course_code' => $p['course_code'],
                    'title' => $p['title'],
                    'min_grade' => $p['min_grade'] ?? 'D'
                ];
            }
        }

        return [
            'passed' => empty($missing),
            'missing' => $missing
        ];
    }
}
