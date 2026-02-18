<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user = $this->getUser();
        // In a real app, you might fetch orders from repository if not eager loaded
        // For now, assuming $user->getOrders() works via relation

        return $this->render('account/index.html.twig', [
            'user' => $user,
        ]);
    }
}
