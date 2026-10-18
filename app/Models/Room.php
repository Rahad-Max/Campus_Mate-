<?php
namespace App\Models;

use App\Core\Model;

class Room extends Model
{
    protected string $table = 'rooms';
    protected array $fillable = [
        'room_number', 'building', 'floor', 'room_type',
        'capacity', 'has_projector', 'has_ac', 'status'
    ];

    public function findByRoomNumber(string $roomNumber): ?array
    {
        return $this->findBy('room_number', $roomNumber);
    }

    public function getAvailableRooms(): array
    {
        return $this->where('status', 'available');
    }

    public function getAvailable(): array
    {
        return $this->getAvailableRooms();
    }

    /**
     * Check if a room is currently referenced by scheduled classes or exams
     */
    public function isReferencedBySchedules(int $roomId): bool
    {
        $classCount = (int)$this->db->fetch(
            "SELECT COUNT(*) as count FROM `class_schedules` WHERE `room_id` = :rid",
            [':rid' => $roomId]
        )['count'];

        $examCount = (int)$this->db->fetch(
            "SELECT COUNT(*) as count FROM `exams` WHERE `room_id` = :rid",
            [':rid' => $roomId]
        )['count'];

        return ($classCount + $examCount) > 0;
    }
}
