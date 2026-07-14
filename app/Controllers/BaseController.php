<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    protected $session;
    protected array $currentUser = [];
    protected int $branchId = 1;
    protected $helpers = ['form', 'url'];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        $this->session = service('session');

        // AuthFilter guarantees this exists for any route under the 'auth' filter group,
        // but guard anyway (e.g. controllers hit directly in a test).
        $this->currentUser = $this->session->get('user') ?? [];
        $this->branchId    = (int) ($this->session->get('branch_id') ?: 1);
    }

    /**
     * Gate a controller action by role permission.
     * Superadmin (Administrator role) bypasses all checks.
     *
     * @param string $action one of: view, add, edit, delete
     */
    protected function requirePermission(string $moduleKey, string $action = 'view'): void
    {
        if (! empty($this->currentUser['is_superadmin'])) {
            return;
        }

        $roleModel = model(\App\Models\RoleModel::class);
        $allowed   = $roleModel->hasPermission((int) $this->currentUser['role_id'], $moduleKey, $action);

        if (! $allowed) {
            $response = service('response');
            $response->setStatusCode(403);
            $response->setBody('<h2>403 - Access Denied</h2><p>You do not have permission to access this module.</p>');
            $response->send();
            exit;
        }
    }
}
