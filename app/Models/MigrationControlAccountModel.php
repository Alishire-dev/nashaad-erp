<?php

namespace App\Models;

use CodeIgniter\Model;

class MigrationControlAccountModel extends Model
{
    protected $table         = 'migration_control_accounts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'status'];

    public function getActive(): array
    {
        return $this->where('status', 'active')->orderBy('name', 'ASC')->findAll();
    }
}
