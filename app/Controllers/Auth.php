<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        $session = service('session');

        if ($session->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        if ($this->request->getMethod() === 'POST') {
            $username = trim((string) $this->request->getPost('username'));
            $password = (string) $this->request->getPost('password');

            $userModel = model(UserModel::class);
            $user = $userModel->attemptLogin($username, $password);

            if ($user) {
                $session->set([
                    'logged_in' => true,
                    'user'      => $user,
                    'branch_id' => $user['branch_id'],
                ]);
                $userModel->touchLastLogin((int) $user['id']);
                return redirect()->to('/dashboard');
            }

            $session->setFlashdata('error', 'Invalid username or password.');
        }

        return $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('X-LiteSpeed-Cache-Control', 'no-cache')
            ->setBody(view('auth/login'));
    }

    public function logout()
    {
        service('session')->destroy();
        return redirect()->to('/login');
    }
}
