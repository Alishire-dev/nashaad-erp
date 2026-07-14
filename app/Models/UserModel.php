<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'branch_id', 'role_id', 'full_name', 'username', 'email',
        'password', 'photo', 'status', 'last_login',
    ];

    protected $useTimestamps = false; // created_at handled manually, no updated_at column here
    protected $createdField  = 'created_at';

    protected $validationRules = [
        'full_name' => 'required|min_length[2]|max_length[100]',
        'username'  => 'required|min_length[3]|max_length[60]|is_unique[users.username,id,{id}]',
        'role_id'   => 'required|is_natural_no_zero',
    ];

    /**
     * Attempt login: returns the user row (with role info) on success, false otherwise.
     */
    public function attemptLogin(string $username, string $password)
    {
        $row = $this->select('users.*, roles.name as role_name, roles.is_superadmin')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.username', $username)
            ->where('users.status', 'active')
            ->first();

        if ($row && password_verify($password, $row['password'])) {
            unset($row['password']);
            return $row;
        }
        return false;
    }

    public function touchLastLogin(int $userId): void
    {
        $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    public function createUser(array $data): int|string|false
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        return $this->insert($data);
    }
}
