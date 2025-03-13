<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\Product;
use App\Entity\OrderDetails;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Loader\Configurator\session;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function cart(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', );
        dump($cart);

        $dataCart = [];
        $total = 0;
        if (!empty($cart)) {
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

    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function removeFromCart($id, SessionInterface $session): Response
    {

        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        $this->addFlash('success', "L'article a ete supprime");
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/delete/', name: 'app_cart_delete')]
    public function cartDeleteAll(SessionInterface $session)
    {
        $session->remove('cart');

        $this->addFlash('success', 'Le panier a ete vide.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/payment', name: 'app_cart_payment')]
    public function cartPayment(SessionInterface $session, ProductRepository $productRepository, EntityManagerInterface $entityManager)
    {
        $cart = $session->get('cart');
        $total = 0;
        dump($cart);

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            $stockDb = $product->getStock();
            // dump($stockDb);

            if ($stockDb < $quantity) {
                if ($stockDb > 0) {
                    // on entre dans la condition si le stock est  insufissant par rapport a la quantite demande
                    dump("article " . $product->getTitle() . " stock insuffisant.");
                    dump("stock restant : " . $stockDb);
                    dump("quantite commandee : " . $quantity);

                    $this->addFlash("danger", "La quantite l'article <strong>" . $product->getTitle() . "</strong> a ete reduit");

                    $cart[$id] = $stockDb;


                } else {
                    // Sinon le stock est a 0, alors on supprime le produit de la session
                    dump("article " . $product->getTitle() . "rupture de stock.");
                    dump("stock restant : " . $stockDb);
                    dump("quantite commande : " . $quantity);

                    $this->addFlash("warning", "L'article <strong>" . $product->getTitle() . "</strong> a ete retirer du panier car nous sommes en rupture de stock");

                    // On supprime l'id  et la quantite du product
                    unset($cart[$id]);
                }

                $error = true;

                $session->set("cart", $cart);
            }
            $total += $product->getPrice() * $quantity;
        }

        // requete INSERT
        if (!isset($error)) {
            $order = new Orders;
            $order->setUser($this->getUser());
            //MINICS 
            $orderNumber = "MINICS- " . date('dmY') . '-' . uniqid();
            $order->setOrderNumber($orderNumber);
            $order->setRising($total);
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setState('En cours de traitement');

            $entityManager->persist($order);
            $entityManager->flush();

            

            foreach ($cart as $id => $quantity) {

                $orderDetails = new OrderDetails;

                $product = $productRepository->find($id);
                $orderDetails->setOrders($order);
                $orderDetails->setProduct($product);
                $orderDetails->setQuantity($quantity);
                $orderDetails->setPrice($product->getPrice());

                $product->setStock($product->getStock() - $quantity);
                
                $entityManager->persist($orderDetails);
                $entityManager->flush();
            }

            $this->addFlash('success' , "Le paiement a ete effectue. Numero de commande <strong>$orderNumber</strong>");
        }

        return $this->redirectToRoute('app_cart');
    }
}
