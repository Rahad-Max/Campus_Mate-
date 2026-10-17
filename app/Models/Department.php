<?php
namespace App\Models;

use App\Core\Model;

class Department extends Model
{
    protected string $table = 'departments';
    protected array $fillable = ['code', 'name', 'faculty_name', 'description', 'status'];

    public function findByCode(string $code): ?array
    {
        return $this->findBy('code', $code);
    }

    public function getActive(): array
    {
        return $this->where('status', 'active');
    }
}
