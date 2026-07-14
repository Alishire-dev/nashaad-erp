<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table         = 'categories';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['branch_id', 'category_code', 'name', 'description', 'status'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
    ];

    public function getForBranch(int $branchId): array
    {
        return $this->where('branch_id', $branchId)->orderBy('id', 'ASC')->findAll();
    }

    /**
     * Creates a category, auto-generating a CAT_0001-style code per branch.
     */
    public function createForBranch(array $data): int|string|false
    {
        $count = $this->where('branch_id', $data['branch_id'])->countAllResults();
        $data['category_code'] = 'CAT_' . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
        $data['created_at']    = date('Y-m-d H:i:s');

        return $this->insert($data);
    }
}
