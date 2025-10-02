<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route( name: 'app_user_index', methods:['GET','POST'])]
    public function index(ProductRepository $productRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            // si one pas connecte on retourne vers login
            return $this->redirectToRoute('app_login');
        }

        // je recupere mes produits ajoutee
        $products = $productRepository->findBy(['owner' => $user]);
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'products' => $products,
        ]);
    }

   
    
}
