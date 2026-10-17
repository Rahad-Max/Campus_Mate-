<?php
namespace App\Services;

use App\Models\Section;

class SeatAvailabilityService
{
    private Section $sectionModel;

    public function __construct()
    {
        $this->sectionModel = new Section();
    }

    /**
     * Check if a section has vacant seats.
     */
    public function isAvailable(int $sectionId): bool
    {
        return $this->sectionModel->hasAvailableSeats($sectionId);
    }

    /**
     * Batch check for multiple sections.
     */
    public function checkBatch(array $sectionIds): array
    {
        $results = [];
        foreach ($sectionIds as $secId) {
            $sec = $this->sectionModel->getDetailedInfo($secId);
            if (!$sec) {
                $results[$secId] = ['available' => false, 'reason' => 'Section not found'];
                continue;
            }

            $available = (int)$sec['enrolled_count'] < (int)$sec['capacity'];
            $results[$secId] = [
                'available' => $available,
                'capacity' => (int)$sec['capacity'],
                'enrolled' => (int)$sec['enrolled_count'],
                'remaining' => max(0, (int)$sec['capacity'] - (int)$sec['enrolled_count']),
                'section_name' => $sec['section_name'],
                'course_code' => $sec['course_code']
            ];
        }
        return $results;
    }
}
