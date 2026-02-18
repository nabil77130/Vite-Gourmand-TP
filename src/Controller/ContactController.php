<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request): Response
    {
        $success = false;

        if ($request->isMethod('POST')) {
            // In a real app, you'd send an email here using Symfony Mailer
            // For now, we just show a success message
            $success = true;
        }

        return $this->render('contact/index.html.twig', [
            'success' => $success,
        ]);
    }
}
