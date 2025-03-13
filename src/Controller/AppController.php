<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $repoProduct): Response
    {

        /* EXO :
            1. Selectionner tout les produits enregistres en BDD (repository)
            2. Transmettre au template les produits selectionnes (render())
            3. Realiser le traitement permettant d'afficher les produits dans le template 'app/index.html.twig'
            4.Creer une nouvelle methode appProductDetails avec le route 'app/product/details/{id}' /app_product_details, nouveau template 'app/product.details.html.twig'
            5. Selectionner en BDD le produit
            6. Afficher les informations du produit (titre, reference, image etc...)
        */
        $dbProduct = $repoProduct->findAll();

        return $this->render('app/index.html.twig', [
            'dbProduct'=> $dbProduct
        ]);
    }

    #[Route('/product/details/{id}', name: 'app_product_details')]
    public function appProductDetails($id, ProductRepository $repoProduct): Response
    {
        $product = $repoProduct->find($id);
        dump($product);
        return $this->render('app/product.details.html.twig', [
            'product'=> $product,

        ]);
        
    }

    #[Route('/products', name: 'app_products')]
    public function appProducts(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        // dump($products);

        return $this->render('admin/index.html.twig', [
            'products'=> $products,
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function appAbout(): Response
    {
        return $this->render('app/about.html.twig', []);
    }

    #[Route('/why_us', name: 'app_why_us')]
    public function appWhyUs(): Response
    {
        return $this->render('app/whyus.html.twig', []);
    }

    #[Route('/testimonials', name: 'app_testimonials')]
    public function appTestimonials(): Response
    {
        return $this->render('app/testimonials.html.twig', []);
    }

    #[Route('/myaccount', name: 'app_my_account')]
    public function appMyAccount(): Response
    {
        return $this->render('app/account.html.twig', []);
    }

   
   
}
