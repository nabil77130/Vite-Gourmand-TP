<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Page de contact.
 *
 * L'enonce demande qu'a la suite de l'envoi du formulaire, "la demande est
 * envoyee par mail a l'entreprise". Le message part donc vers l'adresse de
 * la societe, avec l'adresse du visiteur en "Reply-To" pour que l'equipe
 * puisse lui repondre d'un simple clic.
 */
class ContactController extends AbstractController
{
    /**
     * Adresse de l'entreprise qui recoit les demandes.
     */
    private const COMPANY_EMAIL = 'contact@vite-gourmand.fr';

    /**
     * Libelles lisibles des sujets proposes dans le formulaire.
     */
    private const SUBJECTS = [
        'devis'       => 'Demande de devis',
        'reservation' => 'Réservation',
        'information' => 'Demande d\'information',
        'reclamation' => 'Réclamation',
        'autre'       => 'Autre',
    ];

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(Request $request, MailerInterface $mailer, LoggerInterface $logger): Response
    {
        if ($request->isMethod('POST')) {
            // Protection contre la soumission depuis un autre site.
            if (!$this->isCsrfTokenValid('contact', (string) $request->request->get('_token'))) {
                $this->addFlash('error', 'Votre session a expiré. Merci de renvoyer le formulaire.');

                return $this->redirectToRoute('app_contact');
            }

            $data = [
                'firstName' => trim((string) $request->request->get('firstName')),
                'lastName'  => trim((string) $request->request->get('lastName')),
                'email'     => trim((string) $request->request->get('email')),
                'phone'     => trim((string) $request->request->get('phone')),
                'subject'   => (string) $request->request->get('subject'),
                'eventDate' => (string) $request->request->get('eventDate'),
                'message'   => trim((string) $request->request->get('message')),
            ];

            $errors = $this->validate($data);

            if ($errors !== []) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }

                return $this->redirectToRoute('app_contact');
            }

            $data['subjectLabel'] = self::SUBJECTS[$data['subject']] ?? 'Demande';

            try {
                $email = (new Email())
                    ->from(self::COMPANY_EMAIL)
                    ->to(self::COMPANY_EMAIL)
                    // L'equipe repond directement au visiteur depuis sa messagerie.
                    ->replyTo($data['email'])
                    ->subject(sprintf('[Contact] %s — %s %s', $data['subjectLabel'], $data['firstName'], $data['lastName']))
                    ->html($this->renderView('emails/contact.html.twig', ['data' => $data]));

                $mailer->send($email);

                $this->addFlash('success', 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.');
            } catch (\Throwable $e) {
                $logger->error('Échec de l\'envoi du formulaire de contact : {message}', ['message' => $e->getMessage()]);
                $this->addFlash('error', "Votre message n'a pas pu être envoyé. Merci de réessayer, ou de nous appeler au 01 23 45 67 89.");
            }

            // Redirection apres traitement : rafraichir la page ne renvoie pas le message.
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig');
    }

    /**
     * Controles cote serveur : le "required" du navigateur peut etre contourne.
     *
     * @param array<string, string> $data
     *
     * @return string[]
     */
    private function validate(array $data): array
    {
        $errors = [];

        if ($data['firstName'] === '' || $data['lastName'] === '') {
            $errors[] = 'Merci d\'indiquer votre nom et votre prénom.';
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Merci d\'indiquer une adresse email valide.';
        }

        if (!array_key_exists($data['subject'], self::SUBJECTS)) {
            $errors[] = 'Merci de choisir un sujet.';
        }

        if (mb_strlen($data['message']) < 10) {
            $errors[] = 'Merci de détailler un peu votre demande (10 caractères minimum).';
        }

        return $errors;
    }
}
