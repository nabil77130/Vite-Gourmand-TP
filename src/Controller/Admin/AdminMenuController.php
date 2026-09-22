<?php

namespace App\Controller\Admin;

use App\Entity\Menu;
use App\Entity\MenuImage;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/menu')]
#[IsGranted('ROLE_EMPLOYEE')]
class AdminMenuController extends AbstractController
{
    #[Route('', name: 'admin_menu_index', methods: ['GET'])]
    public function index(MenuRepository $menuRepository): Response
    {
        return $this->render('admin/menu/index.html.twig', [
            'menus' => $menuRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_menu_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->storeUploadedImages($form, $menu);
            $em->persist($menu);
            $em->flush();
            $this->addFlash('success', 'Menu créé avec succès.');
            return $this->redirectToRoute('admin_menu_index');
        }

        return $this->render('admin/menu/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Nouveau menu',
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_menu_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Menu $menu, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->storeUploadedImages($form, $menu);
            $em->flush();
            $this->addFlash('success', 'Menu modifié avec succès.');
            return $this->redirectToRoute('admin_menu_index');
        }

        return $this->render('admin/menu/form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier le menu : ' . $menu->getName(),
            'menu' => $menu,
        ]);
    }

    /**
     * Retire une photo de la galerie d'un menu.
     */
    #[Route('/{id}/image/{imageId}/delete', name: 'admin_menu_image_delete', methods: ['POST'])]
    public function deleteImage(Request $request, Menu $menu, int $imageId, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_image' . $imageId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Action refusée : jeton de sécurité invalide.');

            return $this->redirectToRoute('admin_menu_edit', ['id' => $menu->getId()]);
        }

        // La photo est cherchee dans la galerie de CE menu : impossible de
        // supprimer la photo d'un autre menu en modifiant l'adresse.
        $image = null;
        foreach ($menu->getImages() as $candidate) {
            if ($candidate->getId() === $imageId) {
                $image = $candidate;
                break;
            }
        }

        if ($image === null) {
            throw $this->createNotFoundException('Photo introuvable pour ce menu.');
        }

        // Seules les photos envoyees depuis l'espace employe sont effacees du
        // disque ; les images fournies avec le site restent en place.
        if ($image->isUploaded()) {
            $file = $this->getParameter('kernel.project_dir') . '/public/' . $image->getPath();
            (new Filesystem())->remove($file);
        }

        $menu->removeImage($image);
        $em->flush();

        $this->addFlash('success', 'Photo retirée de la galerie.');

        return $this->redirectToRoute('admin_menu_edit', ['id' => $menu->getId()]);
    }

    #[Route('/{id}/delete', name: 'admin_menu_delete', methods: ['POST'])]
    public function delete(Request $request, Menu $menu, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $menu->getId(), $request->request->get('_token'))) {
            // Les photos envoyees pour ce menu sont effacees du disque avec lui.
            $filesystem = new Filesystem();
            foreach ($menu->getImages() as $image) {
                if ($image->isUploaded()) {
                    $filesystem->remove($this->getParameter('kernel.project_dir') . '/public/' . $image->getPath());
                }
            }
            $em->remove($menu);
            $em->flush();
            $this->addFlash('success', 'Menu supprimé.');
        }
        return $this->redirectToRoute('admin_menu_index');
    }

    /**
     * Enregistre les photos envoyees avec le formulaire et les ajoute a la
     * galerie du menu.
     *
     * Le nom du fichier sur le disque est genere aleatoirement : le nom choisi
     * par l'utilisateur n'est jamais reutilise, ce qui evite les collisions et
     * les noms de fichiers malveillants. L'extension est deduite du contenu
     * reel du fichier, pas de celle annoncee par le navigateur.
     */
    private function storeUploadedImages(FormInterface $form, Menu $menu): void
    {
        /** @var UploadedFile[] $files */
        $files = $form->get('newImages')->getData() ?? [];
        if ($files === []) {
            return;
        }

        $targetDir = $this->getParameter('kernel.project_dir') . '/public/' . MenuImage::UPLOAD_DIR;
        $position = $menu->getNextImagePosition();

        foreach ($files as $file) {
            $filename = bin2hex(random_bytes(12)) . '.' . ($file->guessExtension() ?? 'jpg');
            $file->move($targetDir, $filename);

            $image = new MenuImage();
            $image->setPath(MenuImage::UPLOAD_DIR . '/' . $filename);
            $image->setAlt(sprintf('Photo %d du menu %s', $position + 1, $menu->getName()));
            $image->setPosition($position++);
            $menu->addImage($image);
        }
    }
}
