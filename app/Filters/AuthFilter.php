<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('logged_in')) {
            return redirect()->to('/login');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Authenticated pages must never be cached by LiteSpeed Cache, a CDN,
        // a shared proxy, or the browser itself — both because content here
        // is per-user (caching risks showing one user's page to another),
        // and because it's what was masking every deploy as "no change":
        // LSCache was serving identical stale HTML after each git pull
        // regardless of what the actual files on disk said.
        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('X-LiteSpeed-Cache-Control', 'no-cache');
    }
}
