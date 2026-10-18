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

    /**
     * Find a user by email or username (regardless of is_active status)
     * Used for authentication so status checks can provide accurate feedback.
     */
    public function findByIdentifier(string $identifier): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `email` = :id1 OR `username` = :id2 
                LIMIT 1";
        return $this->db->fetch($sql, [':id1' => $identifier, ':id2' => $identifier]);
    }

    /**
     * Legacy method for finding active users by credentials
     */
    public function findByCredentials(string $identifier): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE (`email` = :id1 OR `username` = :id2) AND `is_active` = 1 
                LIMIT 1";
        return $this->db->fetch($sql, [':id1' => $identifier, ':id2' => $identifier]);
    }

    /**
     * Check if user account is currently active
     */
    public function isAccountActive(array|int $user): bool
    {
        if (is_int($user)) {
            $user = $this->find($user);
        }
        return !empty($user) && (int)($user['is_active'] ?? 0) === 1;
    }

    /**
     * Get user role by user ID
     */
    public function getUserRole(int $userId): ?string
    {
        $user = $this->find($userId);
        return $user['role'] ?? null;
    }

    /**
     * Fetch complete student profile with department and advisor details
     */
    public function getStudentProfile(int $userId): ?array
    {
        $sql = "SELECT s.*, d.code as department_code, d.name as department_name, 
                       u.email, u.first_name, u.last_name, u.phone, u.avatar_url, u.is_active,
                       adv_u.first_name as advisor_first_name, adv_u.last_name as advisor_last_name
                FROM `students` s
                JOIN `users` u ON u.id = s.user_id
                JOIN `departments` d ON d.id = s.department_id
                LEFT JOIN `academic_advisors` aa ON aa.student_id = s.id AND aa.status = 'active'
                LEFT JOIN `teachers` adv_t ON adv_t.id = aa.teacher_id
                LEFT JOIN `users` adv_u ON adv_u.id = adv_t.user_id
                WHERE s.user_id = :uid LIMIT 1";
        return $this->db->fetch($sql, [':uid' => $userId]);
    }

    /**
     * Fetch complete faculty profile with department and profile details
     */
    public function getTeacherProfile(int $userId): ?array
    {
        $sql = "SELECT t.*, d.code as department_code, d.name as department_name,
                       u.email, u.first_name, u.last_name, u.phone, u.avatar_url, u.is_active,
                       fp.bio, fp.research_interests, fp.education, fp.office_hours
                FROM `teachers` t
                JOIN `users` u ON u.id = t.user_id
                JOIN `departments` d ON d.id = t.department_id
                LEFT JOIN `faculty_profiles` fp ON fp.teacher_id = t.id
                WHERE t.user_id = :uid LIMIT 1";
        return $this->db->fetch($sql, [':uid' => $userId]);
    }

    /**
     * Fetch advisor profile (teacher profile with advisee counts)
     */
    public function getAdvisorProfile(int $userId): ?array
    {
        $profile = $this->getTeacherProfile($userId);
        if (!$profile) {
            return null;
        }

        $teacherId = (int)$profile['id'];
        $adviseeCount = (int)$this->db->fetch(
            "SELECT COUNT(*) as count FROM `academic_advisors` WHERE `teacher_id` = :tid AND `status` = 'active'",
            [':tid' => $teacherId]
        )['count'];

        $pendingCount = (int)$this->db->fetch(
            "SELECT COUNT(*) as count FROM `registration_requests` WHERE `advisor_id` = :tid AND `status` = 'submitted'",
            [':tid' => $teacherId]
        )['count'];

        $profile['active_advisees_count'] = $adviseeCount;
        $profile['pending_requests_count'] = $pendingCount;

        return $profile;
    }

    /**
     * Fetch coordinator profile with department administration stats
     */
    public function getCoordinatorProfile(int $userId): ?array
    {
        // Coordinator may also have a teacher record or pure administrative account
        $teacher = $this->getTeacherProfile($userId);
        if ($teacher) {
            return $teacher;
        }

        $sql = "SELECT u.id as user_id, u.email, u.first_name, u.last_name, u.phone, u.role, u.is_active,
                       d.id as department_id, d.code as department_code, d.name as department_name
                FROM `users` u
                LEFT JOIN `departments` d ON d.id = 1
                WHERE u.id = :uid LIMIT 1";
        return $this->db->fetch($sql, [':uid' => $userId]);
    }

    /**
     * Secure password hashing
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    /**
     * Verify password hash
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
