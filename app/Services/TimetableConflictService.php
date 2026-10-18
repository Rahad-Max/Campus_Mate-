<?php
namespace App\Services;

use App\Models\Section;
use App\Models\ClassSchedule;
use App\Models\MakeupClass;
use App\Models\Exam;
use App\Exceptions\AppException;

/**
 * Timetable & Examination Conflict Detection Service
 * Detects teacher, room, section, and student routine clashes.
 */
class TimetableConflictService
{
    private Section $sectionModel;
    private ClassSchedule $scheduleModel;
    private MakeupClass $makeupModel;
    private Exam $examModel;

    public function __construct()
    {
        $this->sectionModel = new Section();
        $this->scheduleModel = new ClassSchedule();
        $this->makeupModel = new MakeupClass();
        $this->examModel = new Exam();
    }

    /**
     * Validate class schedule parameters before creating or updating a slot
     * Throws AppException with detailed diagnostic message if conflict exists.
     */
    public function validateScheduleSlot(
        int $sectionId,
        int $roomId,
        int $teacherId,
        int $semesterId,
        string $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $ignoreScheduleId = null
    ): void {
        // 1. Time range validation
        $this->validateTimeRange($startTime, $endTime);

        // 2. Section Conflict Check
        $secConflict = $this->scheduleModel->findSectionConflict($sectionId, $semesterId, $dayOfWeek, $startTime, $endTime, $ignoreScheduleId);
        if ($secConflict) {
            throw new AppException(
                "Section Conflict: Section {$secConflict['section_name']} ({$secConflict['course_code']}) already has a class scheduled on {$dayOfWeek} from {$secConflict['start_time']} to {$secConflict['end_time']} in Room {$secConflict['room_number']}."
            );
        }

        // 3. Teacher Conflict Check
        $teacherConflict = $this->scheduleModel->findTeacherConflict($teacherId, $semesterId, $dayOfWeek, $startTime, $endTime, $ignoreScheduleId);
        if ($teacherConflict) {
            $tName = trim("{$teacherConflict['first_name']} {$teacherConflict['last_name']}");
            throw new AppException(
                "Teacher Conflict: Faculty member {$tName} already has another class scheduled ({$teacherConflict['course_code']} Sec {$teacherConflict['section_name']}) on {$dayOfWeek} from {$teacherConflict['start_time']} to {$teacherConflict['end_time']}."
            );
        }

        // 4. Room Conflict Check
        $roomConflict = $this->scheduleModel->findRoomConflict($roomId, $semesterId, $dayOfWeek, $startTime, $endTime, $ignoreScheduleId);
        if ($roomConflict) {
            throw new AppException(
                "Room Conflict: Room {$roomConflict['room_number']} is already occupied by {$roomConflict['course_code']} (Section {$roomConflict['section_name']}) on {$dayOfWeek} from {$roomConflict['start_time']} to {$roomConflict['end_time']}."
            );
        }

        // 5. Student Enrollment Collision Warning Check (for enrolled students)
        $studentConflict = $this->scheduleModel->findStudentRegisteredConflict($sectionId, $semesterId, $dayOfWeek, $startTime, $endTime, $ignoreScheduleId);
        if ($studentConflict) {
            throw new AppException(
                "Student Timetable Clash: Student {$studentConflict['first_name']} {$studentConflict['last_name']} ({$studentConflict['student_id_card']}) is already enrolled in {$studentConflict['conflicting_course']} (Sec {$studentConflict['conflicting_section']}) which has class on {$dayOfWeek} {$studentConflict['start_time']} - {$studentConflict['end_time']}."
            );
        }
    }

    /**
     * Validate examination schedule slot
     */
    public function validateExamSlot(
        int $courseOfferingId,
        ?int $sectionId,
        int $roomId,
        ?int $invigilatorTeacherId,
        string $examDate,
        string $startTime,
        string $endTime,
        ?int $ignoreExamId = null
    ): void {
        // 1. Time range validation
        $this->validateTimeRange($startTime, $endTime);

        // 2. Room Conflict Check
        $roomConflict = $this->examModel->findRoomConflict($roomId, $examDate, $startTime, $endTime, $ignoreExamId);
        if ($roomConflict) {
            throw new AppException(
                "Room Conflict: Room {$roomConflict['room_number']} is already reserved for {$roomConflict['course_code']} exam on {$examDate} from {$roomConflict['start_time']} to {$roomConflict['end_time']}."
            );
        }

        // 3. Invigilator Conflict Check
        if ($invigilatorTeacherId) {
            $invConflict = $this->examModel->findInvigilatorConflict($invigilatorTeacherId, $examDate, $startTime, $endTime, $ignoreExamId);
            if ($invConflict) {
                throw new AppException(
                    "Invigilator Conflict: Faculty member is already assigned to invigilate {$invConflict['course_code']} exam on {$examDate} from {$invConflict['start_time']} to {$invConflict['end_time']} in Room {$invConflict['room_number']}."
                );
            }
        }

        // 4. Student Registered Conflict Check
        $studentConflict = $this->examModel->findStudentRegisteredConflict($courseOfferingId, $sectionId, $examDate, $startTime, $endTime, $ignoreExamId);
        if ($studentConflict) {
            throw new AppException(
                "Student Exam Conflict: Student {$studentConflict['first_name']} {$studentConflict['last_name']} ({$studentConflict['student_id_card']}) has another examination ({$studentConflict['conflicting_course']} {$studentConflict['exam_type']}) on {$examDate} from {$studentConflict['start_time']} to {$studentConflict['end_time']}."
            );
        }
    }

    /**
     * Validate makeup class slot against rooms, teachers, and existing regular routine
     */
    public function validateMakeupClassSlot(
        int $sectionId,
        int $roomId,
        int $teacherId,
        int $semesterId,
        string $makeupDate,
        string $startTime,
        string $endTime,
        ?int $ignoreMakeupId = null
    ): void {
        $this->validateTimeRange($startTime, $endTime);

        $dayOfWeek = date('l', strtotime($makeupDate));

        // 1. Check if room is booked by another makeup class
        $roomMakeup = $this->makeupModel->findRoomConflict($roomId, $makeupDate, $startTime, $endTime, $ignoreMakeupId);
        if ($roomMakeup) {
            throw new AppException(
                "Room Conflict: Room {$roomMakeup['room_number']} is already booked for a makeup class ({$roomMakeup['course_code']} Sec {$roomMakeup['section_name']}) on {$makeupDate} from {$roomMakeup['start_time']} to {$roomMakeup['end_time']}."
            );
        }

        // 2. Check if room is occupied by regular class schedule on that day of week
        $roomRoutine = $this->scheduleModel->findRoomConflict($roomId, $semesterId, $dayOfWeek, $startTime, $endTime);
        if ($roomRoutine) {
            throw new AppException(
                "Room Conflict: Room {$roomRoutine['room_number']} is occupied by regular scheduled class {$roomRoutine['course_code']} (Sec {$roomRoutine['section_name']}) on {$dayOfWeek} from {$roomRoutine['start_time']} to {$roomRoutine['end_time']}."
            );
        }

        // 3. Check if room is booked by an exam on that date
        $roomExam = $this->examModel->findRoomConflict($roomId, $makeupDate, $startTime, $endTime);
        if ($roomExam) {
            throw new AppException(
                "Room Conflict: Room {$roomExam['room_number']} is booked for an examination ({$roomExam['course_code']}) on {$makeupDate} from {$roomExam['start_time']} to {$roomExam['end_time']}."
            );
        }

        // 4. Check if teacher has another makeup class
        $teacherMakeup = $this->makeupModel->findTeacherConflict($teacherId, $makeupDate, $startTime, $endTime, $ignoreMakeupId);
        if ($teacherMakeup) {
            throw new AppException(
                "Teacher Conflict: Faculty member already has another makeup class scheduled ({$teacherMakeup['course_code']} Sec {$teacherMakeup['section_name']}) on {$makeupDate} from {$teacherMakeup['start_time']} to {$teacherMakeup['end_time']}."
            );
        }

        // 5. Check if teacher has regular routine class
        $teacherRoutine = $this->scheduleModel->findTeacherConflict($teacherId, $semesterId, $dayOfWeek, $startTime, $endTime);
        if ($teacherRoutine) {
            throw new AppException(
                "Teacher Conflict: Faculty member has regular teaching duties for {$teacherRoutine['course_code']} (Sec {$teacherRoutine['section_name']}) on {$dayOfWeek} from {$teacherRoutine['start_time']} to {$teacherRoutine['end_time']}."
            );
        }
    }

    /**
     * Check if start time is strictly earlier than end time
     */
    public function validateTimeRange(string $startTime, string $endTime): void
    {
        $st = strtotime($startTime);
        $et = strtotime($endTime);

        if ($st === false || $et === false) {
            throw new AppException("Invalid time format. Please provide valid start and end times.");
        }

        if ($st >= $et) {
            throw new AppException("Invalid Time Range: Class start time ({$startTime}) must be strictly earlier than end time ({$endTime}).");
        }

        // Check reasonable class duration (at least 30 minutes, max 5 hours)
        $durationMinutes = ($et - $st) / 60;
        if ($durationMinutes < 30) {
            throw new AppException("Invalid Duration: Class duration must be at least 30 minutes.");
        }

        if ($durationMinutes > 300) {
            throw new AppException("Invalid Duration: Class duration cannot exceed 5 hours.");
        }
    }

    /**
     * Check if a list of sections has internal schedule clashes (Used by Phase 4 Cart)
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

                if ($a['section_id'] === $b['section_id']) {
                    continue;
                }

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
     * Check if proposed sections clash with student's already enrolled courses in the semester (Phase 4 Cart)
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

    public function timesOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        return ($startA < $endB) && ($endA > $startB);
    }
}
