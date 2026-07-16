<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table         = 'customers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['branch_id', 'name', 'phone', 'email', 'address', 'status'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    protected $validationRules = [
        'name' => 'required|min_length[1]|max_length[150]',
    ];

    public function getForBranch(int $branchId): array
    {
        return $this->where('branch_id', $branchId)->orderBy('name', 'ASC')->findAll();
    }

    public function createForBranch(array $data): int|string|false
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    public function walkIn(int $branchId): array
    {
        return $this->where('branch_id', $branchId)->where('name', 'WALK-IN')->first()
            ?? ['id' => null, 'name' => 'WALK-IN'];
    }
}
