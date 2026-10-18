<?php
namespace App\Services;

use App\Core\Database;
use App\Models\ClassSchedule;
use App\Models\Section;
use App\Models\Room;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\ScheduleRevision;
use App\Exceptions\AppException;

/**
 * Timetable Management Service
 * Coordinates schedule slot creation, conflict validation, revisions, publication, and notifications.
 */
class TimetableService
{
    private Database $db;
    private ClassSchedule $scheduleModel;
    private TimetableConflictService $conflictService;
    private NotificationService $notifService;
    private ScheduleRevision $revisionModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->scheduleModel = new ClassSchedule();
        $this->conflictService = new TimetableConflictService();
        $this->notifService = new NotificationService();
        $this->revisionModel = new ScheduleRevision();
    }

    /**
     * Create a new class schedule slot
     */
    public function createScheduleSlot(array $data, int $userId): int
    {
        $sectionId = (int)($data['section_id'] ?? 0);
        $roomId = (int)($data['room_id'] ?? 0);
        $teacherId = (int)($data['teacher_id'] ?? 0);
        $semesterId = (int)($data['semester_id'] ?? 0);
        $dayOfWeek = trim($data['day_of_week'] ?? '');
        $startTime = trim($data['start_time'] ?? '');
        $endTime = trim($data['end_time'] ?? '');
        $classType = trim($data['class_type'] ?? 'lecture');
        $status = in_array($data['status'] ?? '', ['draft', 'published'], true) ? $data['status'] : 'published';

        // 1. Basic entity presence validation
        if (!$sectionId || !$roomId || !$teacherId || !$semesterId || !$dayOfWeek || !$startTime || !$endTime) {
            throw new AppException("All schedule fields (Section, Room, Teacher, Semester, Day, Times) are required.");
        }

        // 2. Validate teacher assignment to section
        $taModel = new TeacherAssignment();
        if (!$taModel->isTeacherAssignedToSection($teacherId, $sectionId)) {
            $teacher = (new Teacher())->find($teacherId);
            $sec = (new Section())->find($sectionId);
            throw new AppException(
                "Teacher Assignment Required: Selected faculty member is not officially assigned to Section {$sec['section_name']}."
            );
        }

        // 3. Server-side conflict detection
        $this->conflictService->validateScheduleSlot(
            $sectionId, $roomId, $teacherId, $semesterId, $dayOfWeek, $startTime, $endTime
        );

        $this->db->beginTransaction();

        try {
            $slotId = $this->scheduleModel->create([
                'section_id' => $sectionId,
                'room_id' => $roomId,
                'teacher_id' => $teacherId,
                'semester_id' => $semesterId,
                'day_of_week' => $dayOfWeek,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'class_type' => $classType,
                'status' => $status
            ]);

            $this->db->commit();

            // 4. Send notifications if published
            if ($status === 'published') {
                $this->notifyScheduleSlotUsers(
                    $slotId,
                    'New Class Routine Published',
                    "A new class schedule has been published for your course: {$dayOfWeek} {$startTime}-{$endTime}."
                );
            }

            return $slotId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing schedule slot with conflict checking and revision tracking
     */
    public function updateScheduleSlot(int $scheduleId, array $data, int $userId, string $reason = ''): bool
    {
        $current = $this->scheduleModel->find($scheduleId);
        if (!$current) {
            throw new AppException("Schedule slot #{$scheduleId} not found.");
        }

        $sectionId = (int)($data['section_id'] ?? $current['section_id']);
        $roomId = (int)($data['room_id'] ?? $current['room_id']);
        $teacherId = (int)($data['teacher_id'] ?? $current['teacher_id']);
        $semesterId = (int)($data['semester_id'] ?? $current['semester_id']);
        $dayOfWeek = trim($data['day_of_week'] ?? $current['day_of_week']);
        $startTime = trim($data['start_time'] ?? $current['start_time']);
        $endTime = trim($data['end_time'] ?? $current['end_time']);
        $classType = trim($data['class_type'] ?? $current['class_type']);

        // Check if there are changes to day, time, or room
        $hasTimeOrRoomChange = ($dayOfWeek !== $current['day_of_week'] ||
                                $startTime !== $current['start_time'] ||
                                $endTime !== $current['end_time'] ||
                                $roomId !== (int)$current['room_id']);

        if ($hasTimeOrRoomChange) {
            $this->conflictService->validateScheduleSlot(
                $sectionId, $roomId, $teacherId, $semesterId, $dayOfWeek, $startTime, $endTime, $scheduleId
            );
        }

        $wasPublished = ($current['status'] === 'published');

        if ($wasPublished && $hasTimeOrRoomChange && empty(trim($reason))) {
            throw new AppException("A documented revision reason is mandatory when modifying a published schedule.");
        }

        $this->db->beginTransaction();

        try {
            // If was published, record revision history
            if ($wasPublished && $hasTimeOrRoomChange) {
                $this->revisionModel->create([
                    'class_schedule_id' => $scheduleId,
                    'previous_day' => $current['day_of_week'],
                    'previous_start_time' => $current['start_time'],
                    'previous_end_time' => $current['end_time'],
                    'previous_room_id' => $current['room_id'],
                    'new_day' => $dayOfWeek,
                    'new_start_time' => $startTime,
                    'new_end_time' => $endTime,
                    'new_room_id' => $roomId,
                    'revision_reason' => trim($reason),
                    'revised_by' => $userId,
                    'effective_date' => date('Y-m-d')
                ]);
            }

            // Update slot
            $this->scheduleModel->update($scheduleId, [
                'section_id' => $sectionId,
                'room_id' => $roomId,
                'teacher_id' => $teacherId,
                'day_of_week' => $dayOfWeek,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'class_type' => $classType
            ]);

            $this->db->commit();

            // Notify if was published
            if ($wasPublished && $hasTimeOrRoomChange) {
                $this->notifyScheduleSlotUsers(
                    $scheduleId,
                    'Class Schedule Revised',
                    "Class routine rescheduled to {$dayOfWeek} {$startTime}-{$endTime}. Reason: " . trim($reason)
                );
            }

            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Publish a draft schedule slot
     */
    public function publishScheduleSlot(int $scheduleId, int $userId): bool
    {
        $slot = $this->scheduleModel->find($scheduleId);
        if (!$slot) {
            throw new AppException("Schedule slot not found.");
        }

        if ($slot['status'] === 'published') {
            return true;
        }

        // Revalidate conflicts before publishing
        $this->conflictService->validateScheduleSlot(
            (int)$slot['section_id'],
            (int)$slot['room_id'],
            (int)$slot['teacher_id'],
            (int)$slot['semester_id'],
            $slot['day_of_week'],
            $slot['start_time'],
            $slot['end_time'],
            $scheduleId
        );

        $this->scheduleModel->update($scheduleId, ['status' => 'published']);

        $this->notifyScheduleSlotUsers(
            $scheduleId,
            'Class Schedule Published',
            "Class routine for {$slot['day_of_week']} {$slot['start_time']}-{$slot['end_time']} is now officially published."
        );

        return true;
    }

    /**
     * Cancel / deactivate a schedule slot
     */
    public function cancelScheduleSlot(int $scheduleId, int $userId, string $reason): bool
    {
        $slot = $this->scheduleModel->find($scheduleId);
        if (!$slot) {
            throw new AppException("Schedule slot not found.");
        }

        if (empty(trim($reason))) {
            throw new AppException("A cancellation reason is required.");
        }

        $this->scheduleModel->update($scheduleId, ['status' => 'cancelled']);

        $this->notifyScheduleSlotUsers(
            $scheduleId,
            'Class Schedule Cancelled',
            "The class routine on {$slot['day_of_week']} {$slot['start_time']}-{$slot['end_time']} has been cancelled. Reason: {$reason}"
        );

        return true;
    }

    /**
     * Helper to dispatch notifications to assigned teacher and enrolled students
     */
    public function notifyScheduleSlotUsers(int $scheduleId, string $title, string $message): void
    {
        $slot = $this->scheduleModel->getScheduleWithDetails($scheduleId);
        if (!$slot) return;

        // 1. Notify Teacher
        $teacherUser = $this->db->fetch("SELECT user_id FROM teachers WHERE id = :tid", [':tid' => $slot['teacher_id']]);
        if ($teacherUser && !empty($teacherUser['user_id'])) {
            $this->notifService->notify(
                (int)$teacherUser['user_id'],
                $title,
                $message,
                'academic',
                '/timetable'
            );
        }

        // 2. Notify Enrolled Students
        $students = $this->db->fetchAll(
            "SELECT u.id as user_id 
             FROM `registrations` reg
             JOIN `students` s ON s.id = reg.student_id
             JOIN `users` u ON u.id = s.user_id
             WHERE reg.section_id = :secid AND reg.status = 'enrolled'",
            [':secid' => $slot['section_id']]
        );

        foreach ($students as $stu) {
            $this->notifService->notify(
                (int)$stu['user_id'],
                $title,
                $message,
                'academic',
                '/timetable'
            );
        }
    }
}
