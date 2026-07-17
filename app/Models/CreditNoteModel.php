<?php

namespace App\Models;

use CodeIgniter\Model;

class CreditNoteModel extends Model
{
    protected $table         = 'credit_notes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'branch_id', 'serial_no', 'sale_id', 'invoice_no', 'customer_id',
        'credit_date', 'total_amount', 'note', 'created_by', 'created_at',
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function getForBranch(int $branchId): array
    {
        return $this->select('credit_notes.*, customers.name as customer_name, users.full_name as created_by_name')
            ->join('customers', 'customers.id = credit_notes.customer_id', 'left')
            ->join('users', 'users.id = credit_notes.created_by', 'left')
            ->where('credit_notes.branch_id', $branchId)
            ->orderBy('credit_notes.id', 'DESC')
            ->findAll();
    }

    private function nextSerial(int $branchId): string
    {
        $year  = date('Y');
        $count = $this->where('branch_id', $branchId)
            ->where("serial_no LIKE 'CN/{$year}/%'", null, false)
            ->countAllResults();

        return sprintf('CN/%s/%05d', $year, $count + 1);
    }

    /**
     * Gets-or-creates the credit note for a cancelled sale — the same
     * underlying record whether it's opened via "Thermal Credit Note" or
     * "A4 Credit Note" first; the second action just reuses the existing
     * one instead of creating a duplicate for the same cancelled invoice.
     */
    public function getOrCreateForSale(array $sale, int $userId): array
    {
        $existing = $this->where('sale_id', $sale['id'])->first();
        if ($existing) {
            return $existing;
        }

        $id = $this->insert([
            'branch_id'    => $sale['branch_id'],
            'serial_no'    => $this->nextSerial($sale['branch_id']),
            'sale_id'      => $sale['id'],
            'invoice_no'   => $sale['invoice_no'],
            'customer_id'  => $sale['customer_id'],
            'credit_date'  => date('Y-m-d'),
            'total_amount' => $sale['grand_total'],
            'note'         => 'Auto-created from cancelled sale ' . $sale['invoice_no'],
            'created_by'   => $userId,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        return $this->find($id);
    }

    /**
     * Manual "Raise Credit Note" path — not tied to any cancelled sale.
     */
    public function raiseManual(int $branchId, ?int $customerId, float $amount, ?string $note, int $userId): int|false
    {
        return $this->insert([
            'branch_id'    => $branchId,
            'serial_no'    => $this->nextSerial($branchId),
            'sale_id'      => null,
            'invoice_no'   => null,
            'customer_id'  => $customerId,
            'credit_date'  => date('Y-m-d'),
            'total_amount' => $amount,
            'note'         => $note,
            'created_by'   => $userId,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}
