<?php

namespace App\Controller\Admin;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/review')]
#[IsGranted('ROLE_EMPLOYEE')]
class AdminReviewController extends AbstractController
{
    #[Route('', name: 'admin_review_index', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepository): Response
    {
        return $this->render('admin/review/index.html.twig', [
            'pending_reviews'  => $reviewRepository->findBy(['status' => 'pending']),
            'approved_reviews' => $reviewRepository->findBy(['status' => 'approved']),
        ]);
    }

    #[Route('/{id}/approve', name: 'admin_review_approve', methods: ['POST'])]
    public function approve(Review $review, EntityManagerInterface $em): Response
    {
        $review->setStatus('approved');
        $em->flush();
        $this->addFlash('success', 'Avis validé et publié.');
        return $this->redirectToRoute('admin_review_index');
    }

    #[Route('/{id}/reject', name: 'admin_review_reject', methods: ['POST'])]
    public function reject(Review $review, EntityManagerInterface $em): Response
    {
        $review->setStatus('rejected');
        $em->flush();
        $this->addFlash('success', 'Avis refusé.');
        return $this->redirectToRoute('admin_review_index');
    }
}
