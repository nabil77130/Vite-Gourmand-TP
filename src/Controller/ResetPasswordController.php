<?php

namespace App\Controller;

use App\Form\NewPasswordType;
use App\Form\ResetPasswordRequestType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Reinitialisation du mot de passe en deux temps :
 *   1. l'utilisateur demande un lien en donnant son adresse email ;
 *   2. il clique sur le lien recu et choisit un nouveau mot de passe.
 *
 * Le jeton est aleatoire, valable une heure et a usage unique.
 */
class ResetPasswordController extends AbstractController
{
    /**
     * Duree de validite du lien envoye par mail.
     */
    private const TOKEN_LIFETIME = '+1 hour';

    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function request(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            // Le compte existe : on genere le jeton et on envoie le lien.
            if ($user !== null && $user->isActive()) {
                $user->setResetToken(bin2hex(random_bytes(32)));
                $user->setResetTokenExpiresAt(new \DateTimeImmutable(self::TOKEN_LIFETIME));
                $em->flush();

                $html = $this->renderView('emails/reset_password.html.twig', [
                    'firstName' => $user->getFirstName(),
                    'resetUrl'  => $this->generateUrl(
                        'app_reset_password',
                        ['token' => $user->getResetToken()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                ]);

                $mailer->send(
                    (new Email())
                        ->from('contact@vite-gourmand.fr')
                        ->to($user->getEmail())
                        ->subject('Réinitialisation de votre mot de passe — Vite & Gourmand')
                        ->html($html)
                );
            }

            // Message identique que le compte existe ou non : on ne revele pas
            // quelles adresses sont inscrites sur le site.
            $this->addFlash(
                'success',
                'Si un compte est associé à cette adresse, un lien de réinitialisation vient de vous être envoyé. Pensez à vérifier vos indésirables.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reinitialiser-mot-de-passe/{token}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function reset(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if ($user === null || !$user->isResetTokenValid()) {
            $this->addFlash('error', 'Ce lien de réinitialisation est invalide ou a expiré. Merci d\'en demander un nouveau.');

            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(NewPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );
            // Le jeton ne doit pas pouvoir resservir.
            $user->clearResetToken();
            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié. Vous pouvez maintenant vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
