<?php

namespace App\Models;

use CodeIgniter\Model;

class SubAccountTypeModel extends Model
{
    protected $table         = 'sub_account_types';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['sub_account_code', 'name', 'account_type_id', 'description', 'status'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function getAll(): array
    {
        return $this->select('sub_account_types.*, account_types.name as account_type_name')
            ->join('account_types', 'account_types.id = sub_account_types.account_type_id', 'left')
            ->orderBy('sub_account_types.id', 'ASC')
            ->findAll();
    }

    public function create(array $data): int|string|false
    {
        $count = $this->countAllResults();
        $data['sub_account_code'] = (string) ($count + 1);
        $data['created_at']       = date('Y-m-d H:i:s');
        return $this->insert($data);
    }
}
