<?php

namespace Mmc\Security\Logout;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    public function onLogoutSuccess(Request $request) {
        return new Response(null, 204);
    }
}
