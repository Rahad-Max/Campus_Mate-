<?php
namespace App\Services;

use App\Models\Section;
use App\Models\ClassSchedule;

class TimetableConflictService
{
    private Section $sectionModel;
    private ClassSchedule $scheduleModel;

    public function __construct()
    {
        $this->sectionModel = new Section();
        $this->scheduleModel = new ClassSchedule();
    }

    /**
     * Check if a list of sections has internal schedule clashes.
     * Returns ['has_conflict' => bool, 'conflicts' => array]
     */
    public function detectInternalConflicts(array $sectionIds): array
    {
        $schedules = [];
        foreach ($sectionIds as $secId) {
            $secSchedules = $this->sectionModel->getSchedules($secId);
            foreach ($secSchedules as $sch) {
                $schedules[] = $sch;
            }
        }

        $conflicts = [];
        $count = count($schedules);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $a = $schedules[$i];
                $b = $schedules[$j];

                // Skip if from same section
                if ($a['section_id'] === $b['section_id']) {
                    continue;
                }

                // If same day, check time overlap
                if ($a['day_of_week'] === $b['day_of_week']) {
                    if ($this->timesOverlap($a['start_time'], $a['end_time'], $b['start_time'], $b['end_time'])) {
                        $conflicts[] = [
                            'day' => $a['day_of_week'],
                            'time_a' => "{$a['start_time']} - {$a['end_time']}",
                            'time_b' => "{$b['start_time']} - {$b['end_time']}",
                            'section_a' => $a['section_id'],
                            'section_b' => $b['section_id']
                        ];
                    }
                }
            }
        }

        return [
            'has_conflict' => !empty($conflicts),
            'conflicts' => $conflicts
        ];
    }

    /**
     * Check if proposed sections clash with student's already enrolled courses in the semester.
     */
    public function detectEnrolledConflicts(int $studentId, int $semesterId, array $proposedSectionIds): array
    {
        $enrolledTimetable = $this->scheduleModel->getStudentTimetable($studentId, $semesterId);
        if (empty($enrolledTimetable)) {
            return ['has_conflict' => false, 'conflicts' => []];
        }

        $proposedSchedules = [];
        foreach ($proposedSectionIds as $secId) {
            foreach ($this->sectionModel->getSchedules($secId) as $sch) {
                $proposedSchedules[] = $sch;
            }
        }

        $conflicts = [];
        foreach ($proposedSchedules as $prop) {
            foreach ($enrolledTimetable as $enrolled) {
                if ($prop['day_of_week'] === $enrolled['day_of_week']) {
                    if ($this->timesOverlap($prop['start_time'], $prop['end_time'], $enrolled['start_time'], $enrolled['end_time'])) {
                        $conflicts[] = [
                            'day' => $prop['day_of_week'],
                            'proposed_time' => "{$prop['start_time']} - {$prop['end_time']}",
                            'enrolled_course' => "{$enrolled['course_code']} ({$enrolled['section_name']})",
                            'enrolled_time' => "{$enrolled['start_time']} - {$enrolled['end_time']}"
                        ];
                    }
                }
            }
        }

        return [
            'has_conflict' => !empty($conflicts),
            'conflicts' => $conflicts
        ];
    }

    private function timesOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        return ($startA < $endB) && ($endA > $startB);
    }
}
