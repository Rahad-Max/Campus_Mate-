<?php
namespace App\Services;

use App\Core\Database;
use App\Models\ClassSchedule;
use App\Exceptions\AppException;

class ScheduleRevisionService
{
    private ClassSchedule $scheduleModel;
    private Database $db;

    public function __construct()
    {
        $this->scheduleModel = new ClassSchedule();
        $this->db = Database::getInstance();
    }

    public function reviseSchedule(
        int $scheduleId,
        string $newDay,
        string $newStartTime,
        string $newEndTime,
        int $newRoomId,
        string $reason,
        int $revisedByUserId
    ): bool {
        $current = $this->scheduleModel->find($scheduleId);
        if (!$current) {
            throw new AppException("Schedule slot not found.");
        }

        // Check for room conflict on the new slot
        $conflict = $this->scheduleModel->findRoomConflict(
            $newRoomId,
            (int)$current['semester_id'],
            $newDay,
            $newStartTime,
            $newEndTime,
            $scheduleId
        );

        if ($conflict) {
            throw new AppException("Room conflict: {$conflict['room_number']} is already occupied by {$conflict['course_code']} ({$conflict['section_name']}) during {$newDay} {$newStartTime}-{$newEndTime}.");
        }

        $this->db->beginTransaction();

        try {
            // 1. Record revision history
            $sql = "INSERT INTO `schedule_revisions` 
                    (`class_schedule_id`, `previous_day`, `previous_start_time`, `previous_end_time`, `previous_room_id`,
                     `new_day`, `new_start_time`, `new_end_time`, `new_room_id`, `revision_reason`, `revised_by`, `effective_date`)
                    VALUES (:csid, :pday, :pst, :pet, :proom, :nday, :nst, :net, :nroom, :reason, :by, :edate)";

            $this->db->query($sql, [
                ':csid' => $scheduleId,
                ':pday' => $current['day_of_week'],
                ':pst' => $current['start_time'],
                ':pet' => $current['end_time'],
                ':proom' => $current['room_id'],
                ':nday' => $newDay,
                ':nst' => $newStartTime,
                ':net' => $newEndTime,
                ':nroom' => $newRoomId,
                ':reason' => $reason,
                ':by' => $revisedByUserId,
                ':edate' => date('Y-m-d')
            ]);

            // 2. Update current schedule slot
            $this->scheduleModel->update($scheduleId, [
                'day_of_week' => $newDay,
                'start_time' => $newStartTime,
                'end_time' => $newEndTime,
                'room_id' => $newRoomId
            ]);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
