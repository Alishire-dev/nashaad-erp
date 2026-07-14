<?php

namespace App\Models;

use CodeIgniter\Model;

class TaxCategoryModel extends Model
{
    protected $table         = 'tax_categories';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'rate', 'status'];

    public function getActive(): array
    {
        return $this->where('status', 'active')->orderBy('id', 'ASC')->findAll();
    }
}
