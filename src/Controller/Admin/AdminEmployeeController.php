<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\EmployeeType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des comptes employes, reservee a l'administrateur.
 *
 * Trois regles de l'enonce sont appliquees ici :
 *   - l'administrateur fournit l'email et le mot de passe du compte ;
 *   - l'employe recoit un mail l'informant de la creation, SANS le mot de
 *     passe : il doit se rapprocher de l'administrateur pour l'obtenir ;
 *   - un compte employe peut etre rendu inutilisable en cas de depart.
 *
 * Le role est impose par le serveur : aucun compte administrateur ne peut
 * etre cree depuis l'application.
 */
#[Route('/admin/employee')]
#[IsGranted('ROLE_ADMIN')]
class AdminEmployeeController extends AbstractController
{
    #[Route('', name: 'admin_employee_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/employee/index.html.twig', [
            'employees' => $userRepository->findEmployees(),
        ]);
    }

    #[Route('/new', name: 'admin_employee_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer,
        LoggerInterface $logger
    ): Response {
        $employee = new User();
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Role impose cote serveur : jamais ROLE_ADMIN.
            $employee->setRoles(['ROLE_EMPLOYEE']);
            $employee->setIsActive(true);
            $employee->setPassword(
                $passwordHasher->hashPassword($employee, $form->get('plainPassword')->getData())
            );

            $em->persist($employee);
            $em->flush();

            $this->notifyNewEmployee($employee, $mailer, $logger);

            $this->addFlash('success', sprintf(
                'Le compte de %s a été créé. Communiquez-lui son mot de passe de vive voix : il ne figure pas dans l\'email.',
                $employee->getFirstName()
            ));

            return $this->redirectToRoute('admin_employee_index');
        }

        return $this->render('admin/employee/form.html.twig', [
            'form'  => $form->createView(),
            'title' => 'Nouveau compte employé',
        ]);
    }

    /**
     * Active ou desactive un compte employe.
     *
     * La desactivation conserve le compte et tout son historique ; seule la
     * connexion est refusee, par App\Security\UserChecker.
     */
    #[Route('/{id}/toggle', name: 'admin_employee_toggle', methods: ['POST'])]
    public function toggle(Request $request, User $user, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('toggle' . $user->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Action non autorisée.');

            return $this->redirectToRoute('admin_employee_index');
        }

        // Garde-fou : on ne touche pas aux comptes administrateur, ni au sien.
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $this->addFlash('error', 'Un compte administrateur ne peut pas être désactivé depuis l\'application.');

            return $this->redirectToRoute('admin_employee_index');
        }

        $user->setIsActive(!$user->isActive());
        $em->flush();

        $this->addFlash('success', $user->isActive()
            ? sprintf('Le compte de %s a été réactivé.', $user->getFirstName())
            : sprintf('Le compte de %s a été désactivé : il ne peut plus se connecter.', $user->getFirstName()));

        return $this->redirectToRoute('admin_employee_index');
    }

    /**
     * Informe l'employe de la creation de son compte, sans lui transmettre
     * le mot de passe.
     */
    private function notifyNewEmployee(User $employee, MailerInterface $mailer, LoggerInterface $logger): void
    {
        try {
            $html = $this->renderView('emails/employee_account.html.twig', [
                'firstName' => $employee->getFirstName(),
                'email'     => $employee->getEmail(),
                'loginUrl'  => $this->generateUrl('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            $mailer->send(
                (new Email())
                    ->from('contact@vite-gourmand.fr')
                    ->to($employee->getEmail())
                    ->subject('Votre compte employé Vite & Gourmand a été créé')
                    ->html($html)
            );
        } catch (\Throwable $e) {
            $logger->error('Email de création de compte non envoyé à {email} : {message}', [
                'email'   => $employee->getEmail(),
                'message' => $e->getMessage(),
            ]);
            $this->addFlash('warning', "Le compte a bien été créé, mais l'email de notification n'a pas pu être envoyé.");
        }
    }
}
