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
        return $this->findBy('code', $code);
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
}
