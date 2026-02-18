<?php

namespace App\Controller\Admin;

use App\Entity\Horaire;
use App\Form\HoraireType;
use App\Repository\HoraireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/horaire')]
#[IsGranted('ROLE_EMPLOYEE')]
class AdminHoraireController extends AbstractController
{
    #[Route('', name: 'admin_horaire_index', methods: ['GET'])]
    public function index(HoraireRepository $horaireRepository): Response
    {
        return $this->render('admin/horaire/index.html.twig', [
            'horaires' => $horaireRepository->findAll(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_horaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Horaire $horaire, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(HoraireType::class, $horaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Horaire mis à jour.');
            return $this->redirectToRoute('admin_horaire_index');
        }

        return $this->render('admin/horaire/edit.html.twig', [
            'form' => $form->createView(),
            'horaire' => $horaire,
        ]);
    }
}
