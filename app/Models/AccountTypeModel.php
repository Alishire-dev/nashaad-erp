<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountTypeModel extends Model
{
    protected $table         = 'account_types';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'status'];

    public function getAll(): array
    {
        return $this->orderBy('name', 'ASC')->findAll();
    }
}
