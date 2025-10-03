<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product')]
final class ProductController extends AbstractController
{
    // Page d'accueil avec tout les produits
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    // Page ajout d'un produit
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du fichier envoyé depuis formulaire
            $file = ($form->get('img')->getData());
            if($file)
                // génération d'un nouveau nom de fichier, plus ajout d'un timestamp devant le nom du fichier
                {
                    $newFileName = time() . '-' . $file->getClientOriginalName();
                    // Déplace le fichier uploadé dans le fichier défini
                    $file->move($this->getParameter('product_dir'), $newFileName);
                    // Enrengistre le nom du fichier
                    $product->setImg($newFileName);
                }
            
            // Associe le produit au user connecté
            $product->setOwner($this->getUser());

            $entityManager->persist($product);
            $entityManager->flush();
            // Si ajout réussi, renvoi vers l'index
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    // Page d'un article
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        // Récupération de tous les autres produits du propriétaire
        // Récupére le propriétaire du produit
        $owner = $product->getOwner();
        // Récupére les produits proposé par le propriétaire
        $ownerProducts = $owner->getProducts();

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'ownerProducts' => $ownerProducts,//new
        ]);
    }

    // Editer/modifier un produit
    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            
            // Récupération du fichier envoyé depuis formulaire
            $file = ($form->get('img')->getData());
            if($file)
                // génération d'un nouveau nom de fichier, plus ajout d'un timestamp devant le nom du fichier
                {
                    $newFileName = time() . '-' . $file->getClientOriginalName();
                    // Déplace le fichier uploadé dans le fichier défini
                    $file->move($this->getParameter('product_dir'), $newFileName);
                    // Enrengistre le nom du fichier
                    $product->setImg($newFileName);
                }
            
            // Associe le produit au user connecté
            $product->setOwner($this->getUser());

            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    // Demande d'emprunt
    #[Route('/{id}/request', name: 'app_product_request', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function request(Product $product, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Vérifie que le user n'est pas le owner
        if($product->getOwner() === $user) 
        {
            $this->addFlash('Erreur', 'Vous ne pouvez pas emprunter/demandé votre prope produit');
            return $this->redirectToRoute('app_product_index');
        }

        //  Vérifie si produit est déjà demandé
        if($product->getBorrower() !== null)
        {
            $this->addFlash('Erreur', 'Ce produit est déja emprunté/emprunté');
            return $this->redirectToRoute('app_product_index');
        }

        // Assigner le borrower (demande en attente)
        $product->setBorrower($user);

        $em->flush();

        $this->addFlash('Bravo!', 'Demande de location envoyée !');
        return $this->redirectToRoute('app_product_index');
    }   

    // User(Owner) décide ou pas d'autoriser l'emprunt
    #[Route('/{id}/decision', name: 'app_product_decision', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function decision(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Vérifie que le user est bien Owner 
        if ($product->getOwner() !== $user) 
        {
            throw $this->createAccessDeniedException('Seul le propriétaire peut prendre la décision');
        }

        // Valide ou Refus
        $action = $request->request->get('action');

        if ($action === 'accept') {
            // Validation, demande acceptée
            $this->addFlash('Bravo!', 'La demande a été acceptée');
        } elseif ($action === 'reject') {
            // Refus de la demande, la valeur redevient null
            $product->setBorrower(null);
            $this->addFlash('Bravo','La demande a été refusée');
        }

        $em->flush();
        return $this->redirectToRoute('app_product_index');
    }


    // Supprimer un produit
    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
