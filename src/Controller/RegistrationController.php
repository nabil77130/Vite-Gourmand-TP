<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, MailerInterface $mailer, LoggerInterface $logger): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // Envoi du mail de bienvenue
            $html = $this->renderView('emails/welcome.html.twig', [
                'firstName' => $user->getFirstName(),
                'menuUrl' => $this->generateUrl('app_menu', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
            $email = (new Email())
                ->from('contact@vite-gourmand.fr')
                ->to($user->getEmail())
                ->subject('Bienvenue chez Vite & Gourmand !')
                ->html($html);
            // Le compte est deja cree : un echec d'envoi ne doit pas bloquer
            // l'inscription ni afficher une page d'erreur a l'utilisateur.
            try {
                $mailer->send($email);
            } catch (\Throwable $e) {
                $logger->error('Email de bienvenue non envoyé à {email} : {message}', [
                    'email'   => $user->getEmail(),
                    'message' => $e->getMessage(),
                ]);
                $this->addFlash('warning', "Votre compte est bien créé, mais l'email de bienvenue n'a pas pu être envoyé.");
            }

            return $security->login($user, AppAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
