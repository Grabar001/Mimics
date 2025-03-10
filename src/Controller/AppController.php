<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('app/index.html.twig', []);
    }

    #[Route('/products', name: 'app_products')]
    public function appProducts(): Response
    {
        return $this->render('app/products.html.twig', []);
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
