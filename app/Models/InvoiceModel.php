<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table         = 'invoices';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'branch_id', 'customer_id', 'invoice_no', 'invoice_date', 'due_date',
        'note', 'revenue_account_id', 'grand_total', 'amount_paid', 'pay_status',
        'created_by', 'created_at',
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    private function nextInvoiceNo(int $branchId): string
    {
        $year  = date('Y');
        $count = $this->where('branch_id', $branchId)
            ->where("invoice_no LIKE 'INVC/{$year}/%'", null, false)
            ->countAllResults();

        return sprintf('INVC/%s/%05d', $year, $count + 1);
    }

    public function getForBranch(int $branchId): array
    {
        return $this->select('invoices.*, customers.name as customer_name, users.full_name as created_by_name')
            ->join('customers', 'customers.id = invoices.customer_id', 'left')
            ->join('users', 'users.id = invoices.created_by', 'left')
            ->where('invoices.branch_id', $branchId)
            ->orderBy('invoices.id', 'DESC')
            ->findAll();
    }

    /**
     * Creates the invoice and posts a matching ledger entry against the
     * chosen Revenue/Income account (money in, same auto-posting rule as
     * every other real transaction in this app) — an invoice not tied to
     * an account wouldn't show up anywhere in Accounting otherwise.
     */
    public function createInvoice(array $data, int $userId): int|false
    {
        $this->db->transStart();

        $data['invoice_no'] = trim($data['invoice_no'] ?? '') !== '' ? $data['invoice_no'] : $this->nextInvoiceNo($data['branch_id']);
        $data['created_by'] = $userId;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['pay_status'] = 'unpaid';
        $data['amount_paid'] = 0;

        $id = $this->insert($data);

        if ($id && ! empty($data['revenue_account_id'])) {
            $account = $this->db->table('chart_of_accounts')->where('id', $data['revenue_account_id'])->get()->getRowArray();
            if ($account) {
                model(MoneyTransactionModel::class)->post(
                    $data['branch_id'],
                    $userId,
                    $data['invoice_date'],
                    $account['account_name'],
                    (float) $data['grand_total'],
                    0,
                    'Invoice ' . $data['invoice_no'],
                    'invoice',
                    'invoice',
                    $id,
                    $data['invoice_no']
                );
            }
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? $id : false;
    }
}
