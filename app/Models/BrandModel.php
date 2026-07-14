<?php

namespace App\Models;

use CodeIgniter\Model;

class BrandModel extends Model
{
    protected $table         = 'brands';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['branch_id', 'name', 'status'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    protected $validationRules = [
        'name' => 'required|min_length[1]|max_length[100]',
    ];

    public function getForBranch(int $branchId): array
    {
        return $this->where('branch_id', $branchId)->orderBy('id', 'ASC')->findAll();
    }

    public function createForBranch(array $data): int|string|false
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }
}
