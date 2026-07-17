<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table         = 'units';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['branch_id', 'name', 'short_name', 'conversion_factor', 'base_unit_id', 'description', 'status'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    protected $validationRules = [
        'name'       => 'required|min_length[1]|max_length[60]',
        'short_name' => 'required|min_length[1]|max_length[20]',
    ];

    public function getForBranch(int $branchId): array
    {
        return $this->select('units.*, base.name as base_unit_name')
            ->join('units as base', 'base.id = units.base_unit_id', 'left')
            ->where('units.branch_id', $branchId)
            ->orderBy('units.id', 'ASC')
            ->findAll();
    }

    public function createForBranch(array $data): int|string|false
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }
}
