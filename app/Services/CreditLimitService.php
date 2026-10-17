<?php
namespace App\Services;

use App\Models\Student;

class CreditLimitService
{
    private Student $studentModel;
    private array $policy;

    public function __construct()
    {
        $this->studentModel = new Student();
        $config = require dirname(__DIR__, 2) . '/config/app.php';
        $this->policy = $config['academic'] ?? [
            'min_credits_per_semester' => 9.0,
            'max_credits_per_semester' => 18.0,
            'probation_max_credits' => 12.0
        ];
    }

    /**
     * Validate whether proposed credits are within university limits for this student.
     * Returns ['valid' => bool, 'min' => float, 'max' => float, 'requested' => float, 'message' => ?string]
     */
    public function validate(int $studentId, float $totalRequestedCredits): array
    {
        $student = $this->studentModel->find($studentId);
        $min = (float)$this->policy['min_credits_per_semester'];
        $max = (float)$this->policy['max_credits_per_semester'];

        // If student is on academic probation (CGPA < 2.50 or status probation), enforce probation cap
        $cgpa = (float)($student['cgpa'] ?? 0.0);
        $status = $student['academic_status'] ?? 'regular';

        if ($status === 'probation' || ($cgpa > 0 && $cgpa < 2.50)) {
            $max = (float)$this->policy['probation_max_credits'];
        }

        if ($totalRequestedCredits < $min) {
            return [
                'valid' => false,
                'min' => $min,
                'max' => $max,
                'requested' => $totalRequestedCredits,
                'message' => "Requested credits ({$totalRequestedCredits}) are below the minimum semester threshold ({$min} cr)."
            ];
        }

        if ($totalRequestedCredits > $max) {
            return [
                'valid' => false,
                'min' => $min,
                'max' => $max,
                'requested' => $totalRequestedCredits,
                'message' => "Requested credits ({$totalRequestedCredits}) exceed the maximum allowable limit ({$max} cr) for your academic standing."
            ];
        }

        return [
            'valid' => true,
            'min' => $min,
            'max' => $max,
            'requested' => $totalRequestedCredits,
            'message' => null
        ];
    }
}
