<?php

namespace App\Controller;
use App\Service\Cart;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/eshop/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, ProductsRepository $productsRepository,
                          SessionInterface $session,Cart $cart): Response
    {
        if($this->getUser())
        {
            $this->addFlash('error', 'You are already signed in.');
            return $this->redirectToRoute('index');
        }

        $chosenProducts = $cart->retrieveCartItems($session,$productsRepository);
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error,
            'chosenProducts' =>
                $chosenProducts[0], 'totalCartPrice' => $chosenProducts[1], 'totalQuantity' => $chosenProducts[2],]);
    }

    /**
     * @Route("/eshop/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
