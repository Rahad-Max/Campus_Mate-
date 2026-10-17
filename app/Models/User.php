<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = [
        'username', 'email', 'password_hash', 'role',
        'first_name', 'last_name', 'phone', 'avatar_url',
        'is_active', 'last_login_at'
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    public function findByUsername(string $username): ?array
    {
        return $this->findBy('username', $username);
    }

    public function findByCredentials(string $identifier): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE (`email` = :id1 OR `username` = :id2) AND `is_active` = 1 LIMIT 1";
        return $this->db->fetch($sql, [':id1' => $identifier, ':id2' => $identifier]);
    }

    public function getStudentProfile(int $userId): ?array
    {
        $sql = "SELECT s.*, d.code as department_code, d.name as department_name, 
                       u.email, u.first_name, u.last_name, u.phone
                FROM `students` s
                JOIN `users` u ON u.id = s.user_id
                JOIN `departments` d ON d.id = s.department_id
                WHERE s.user_id = :uid LIMIT 1";
        return $this->db->fetch($sql, [':uid' => $userId]);
    }

    public function getTeacherProfile(int $userId): ?array
    {
        $sql = "SELECT t.*, d.code as department_code, d.name as department_name,
                       u.email, u.first_name, u.last_name, u.phone,
                       fp.bio, fp.research_interests, fp.education, fp.office_hours
                FROM `teachers` t
                JOIN `users` u ON u.id = t.user_id
                JOIN `departments` d ON d.id = t.department_id
                LEFT JOIN `faculty_profiles` fp ON fp.teacher_id = t.id
                WHERE t.user_id = :uid LIMIT 1";
        return $this->db->fetch($sql, [':uid' => $userId]);
    }
}
