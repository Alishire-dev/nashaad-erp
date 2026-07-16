<?php

namespace App\Models;

use CodeIgniter\Model;

class ChartOfAccountModel extends Model
{
    protected $table         = 'chart_of_accounts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['branch_id', 'account_name', 'gl_code', 'sub_account_type_id', 'description', 'status'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function getForBranch(int $branchId): array
    {
        return $this->select('chart_of_accounts.*, sub_account_types.name as sub_account_name,
                account_types.name as account_type_name')
            ->join('sub_account_types', 'sub_account_types.id = chart_of_accounts.sub_account_type_id', 'left')
            ->join('account_types', 'account_types.id = sub_account_types.account_type_id', 'left')
            ->where('chart_of_accounts.branch_id', $branchId)
            ->orderBy('chart_of_accounts.gl_code', 'ASC')
            ->findAll();
    }

    public function getActive(int $branchId): array
    {
        return $this->where('branch_id', $branchId)->where('status', 'active')->orderBy('account_name', 'ASC')->findAll();
    }

    public function createForBranch(array $data): int|string|false
    {
        $count = $this->countAllResults();
        $data['gl_code']    = 'GL' . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    public function findByName(int $branchId, string $name): ?array
    {
        return $this->where('branch_id', $branchId)->where('account_name', $name)->first();
    }
}
