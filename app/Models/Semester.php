<?php
namespace App\Models;

use App\Core\Model;

class Semester extends Model
{
    protected string $table = 'semesters';
    protected array $fillable = [
        'code', 'name', 'academic_year', 'term',
        'start_date', 'end_date', 'registration_start',
        'registration_end', 'add_drop_deadline',
        'is_current', 'status'
    ];

    public function getCurrent(): ?array
    {
        return $this->findBy('is_current', 1);
    }

    public function findByCode(string $code): ?array
    {
        return $this->findBy('code', strtoupper(trim($code)));
    }

    public function getAllOrdered(): array
    {
        return $this->all('academic_year DESC, FIELD(term, "Fall", "Summer", "Spring"), start_date DESC');
    }

    public function isRegistrationOpen(int $semesterId): bool
    {
        $sem = $this->find($semesterId);
        if (!$sem) {
            return false;
        }

        $now = new \DateTime();
        $start = new \DateTime($sem['registration_start']);
        $end = new \DateTime($sem['registration_end']);

        return ($sem['status'] === 'registration_open') && ($now >= $start && $now <= $end);
    }

    public function isCodeUnique(string $code, ?int $excludeId = null): bool
    {
        $code = strtoupper(trim($code));
        $sql = "SELECT id FROM `{$this->table}` WHERE `code` = :code";
        $params = [':code' => $code];

        if ($excludeId !== null) {
            $sql .= " AND `id` != :id";
            $params[':id'] = $excludeId;
        }

        $row = $this->db->fetch($sql, $params);
        return empty($row);
    }

    /**
     * Enforce single active/current semester system-wide
     */
    public function setCurrent(int $semesterId): bool
    {
        $this->db->beginTransaction();
        try {
            // Remove current flag from all semesters
            $this->db->execute("UPDATE `{$this->table}` SET `is_current` = 0");
            // Set current flag for this semester
            $updated = $this->db->execute(
                "UPDATE `{$this->table}` SET `is_current` = 1, `status` = 'active' WHERE `id` = :id",
                [':id' => $semesterId]
            );
            $this->db->commit();
            return $updated > 0;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Validate semantic date ranges
     */
    public static function validateDates(
        string $startDate,
        string $endDate,
        ?string $regStart = null,
        ?string $regEnd = null,
        ?string $addDrop = null
    ): array {
        $errors = [];

        $dStart = strtotime($startDate);
        $dEnd = strtotime($endDate);

        if (!$dStart || !$dEnd) {
            $errors[] = "Semester start date and end date must be valid dates.";
        } elseif ($dEnd <= $dStart) {
            $errors[] = "Semester end date must be after the semester start date.";
        }

        if (!empty($regStart) && !empty($regEnd)) {
            $dRegStart = strtotime($regStart);
            $dRegEnd = strtotime($regEnd);
            if (!$dRegStart || !$dRegEnd) {
                $errors[] = "Registration start and end dates must be valid dates.";
            } elseif ($dRegEnd <= $dRegStart) {
                $errors[] = "Registration end date must be after registration start date.";
            }

            if (!empty($addDrop)) {
                $dAddDrop = strtotime($addDrop);
                if ($dAddDrop && $dAddDrop < $dRegEnd) {
                    $errors[] = "Add/Drop deadline cannot be earlier than registration end date.";
                }
            }
        }

        return $errors;
    }

    /**
     * Check if semester has dependent course offerings or registrations
     */
    public function hasAcademicReferences(int $semesterId): bool
    {
        $offerings = (int)$this->db->fetch("SELECT COUNT(*) as c FROM `course_offerings` WHERE `semester_id` = :id", [':id' => $semesterId])['c'];
        $history = (int)$this->db->fetch("SELECT COUNT(*) as c FROM `student_course_history` WHERE `semester_id` = :id", [':id' => $semesterId])['c'];
        $requests = (int)$this->db->fetch("SELECT COUNT(*) as c FROM `registration_requests` WHERE `semester_id` = :id", [':id' => $semesterId])['c'];

        return ($offerings + $history + $requests) > 0;
    }
}
