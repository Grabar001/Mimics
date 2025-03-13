<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function cart(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', );
        dump( $cart );

        $dataCart =[];
        $total = 0;
        if(!empty($cart)){
            foreach ($cart as $id => $quantity) {
                $product = $productRepository->find($id);
                dump($product);
                $dataCart[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                ];
                $total += $product->getPrice() * $quantity;
            }
        }
        

        return $this->render('cart/index.html.twig', [
            'dataCart' => $dataCart,
            'total' => $total,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function cartAdd(Request $request, Product $product, SessionInterface $session)
    {

        // Creation du panier dans la session
        $cart = $session->get('cart', []);
        $id = $product->getId();
        $quantity = $request->request->get('quantity');


        // dump($id);
        // dump($quantity);

        if (!empty($cart[$id])) {
            // dump('if produit deja dans le panier');
            $cart[$id] = $cart[$id] + $quantity;
        } else {
            // dump('else produit pas dans le panier');
            $cart[$id] = $quantity;
        }

        $session->set("cart", $cart);
        // dump($cart);

        return $this->redirectToRoute('app_cart');
    }
}
