<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table         = 'roles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'description', 'is_superadmin', 'status'];

    /**
     * @param string $action one of: view, add, edit, delete
     */
    public function hasPermission(int $roleId, string $moduleKey, string $action = 'view'): bool
    {
        $col = 'can_' . $action;

        $row = $this->db->table('role_permissions')
            ->select($col)
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $roleId)
            ->where('permissions.module_key', $moduleKey)
            ->get()
            ->getRowArray();

        return $row ? (bool) $row[$col] : false;
    }
}
