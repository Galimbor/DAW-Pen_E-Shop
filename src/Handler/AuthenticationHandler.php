<?php


namespace App\Handler;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class AuthenticationHandler implements  LogoutSuccessHandlerInterface
{



    public function onLogoutSuccess(Request $request): Response
    {

        $referer = $request->headers->get('referer');
        $request->getSession()->getFlashBag()->add('success', 'You have successfully logged out.');
        return new RedirectResponse($request->getBaseUrl());
//       return new RedirectResponse($referer);

    }
}