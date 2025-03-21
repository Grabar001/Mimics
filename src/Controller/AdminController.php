<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Product;
use App\Entity\Category;
use App\Form\ProductFormType;
use App\Form\CategoryFormType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function admin(): Response
    {
        return $this->render('admin/index.html.twig', []);
    }

    #[Route('/admin/products', name: 'app_admin_products')]
    #[Route('/admin/products/update/{id}', name: 'app_admin_products_update')]
    public function adminProducts(?Product $product, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ProductRepository $productRepository): Response
    {
        // ?Product $product : le ? veut dire que par défaut $product a une valeur null
        // dump($product);

        // 1er route '/admin/products'
        // Si la variable $product N'EST PAS (!), si elle renvoie false, cela veut dire qu'aucun id product n'est passé dans l'URL, alors on entre dans la condition, et on initialise un objet Entity $product, donc c'est une insertion de produit

        // 2ème route : '/admin/products/update/{id}'
        // On envoi un id $product dans l'URL, Symfony comprend que l'on a besoin d'un objet entity product issu de la table SQL product, il est capable automatiquement d'aller sélectionner en BDD le produit et de l'envoyer en argument de la fonction ?Product $product, à ce moment là, la variable $product contient les données du produit que l'on souhaite modifié, alors on ne rentre pas dans la condition if

        if (!$product)
            $product = new Product;

        $form = $this->createForm(ProductFormType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pictureFile = $form->get('picture')->getData();
            // dump($pictureFile);

            if ($pictureFile) {
                // retourne le nom du fichier d'origine (sans l'extension)
                $originalFileName = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // dump($originalFileName);

                // slug() securise le nom du fichier (suppression espace etc...)
                $safeFileName = $slugger->slug($originalFileName);
                // dump($safeFileName);

                // On renomme l'image
                //              p2-645673576.png
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $pictureFile->guessExtension();
                // dump($newFileName);
                // dump($this->getParameter('image_directory'));
                $currentPath = $this->getParameter('image_directory');

                try {
                    $pictureFile->move($currentPath, $newFileName);
                } catch (FileException $e) {
                    // dump($e);
                }

                $product->setPicture($newFileName);
                // dump($product);
            }

            //Si la condition retourne TRUE, cela veut dire que l'on est en train de modifier un produit
            if (!$product->getId()) {
                $messageValidate = "Les modifications ons ete enregistrees";
            } else {
                $messageValidate = "L'article a bien ete enregistre";
            }

            $product->setCreatedAt(new DateTimeImmutable());
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', "L'article a bien été enregistré");

            return $this->redirectToRoute('app_admin_products');
        }

        /*
            Exo :
            1. selectionner tout les produits enregistrés en BDD (repository)
             
            2. Transmettre les donnees selectionnées au template
            3. Afficher les donnes sous forme de tableau HTML dans le template 'admin/products.html.twig (boucle {% for %})
            4. Prevoir pour chaque produit, deux liens modification / suppression
        */
        $products = $productRepository->findAll();
        $pictureFile = null;


        return $this->render('admin/products.html.twig', [
            'productForm' => $form->createView(),
            'products' => $products,
            'product' => $product,
            'pictureFile' => $product->getPicture(),
        ]);
        // dump($products);
    }


    #[Route('/admin/products/remove/{id}', name: 'app_admin_products_remove')]
    public function adminRemoveProduct($id, ProductRepository $repoProduct, EntityManagerInterface $entityManager)
    {
        $product = $repoProduct->find($id);
        dump($product);

        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash('success', "L'article a ete supprime.");

        return $this->redirectToRoute('app_admin_products');
    }

    #[Route('/admin/category', name: 'app_admin_category')]
    public function adminCategory(Request $request, EntityManagerInterface $entityManager, CategoryRepository $repoCategory): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryFormType::class, $category);

        // $category->setTitle($_POST['title']);
        // $category->setTitle($_POST['description']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $category->setCreatedAt(new DateTimeImmutable());

            // $stmt->prepare("INSERT INTO category VALUES (:title)");
            //$stmt->bindValue(':title', $category->getTitle()), PDO::PARAM_STR);
            $entityManager->persist($category);

            // $stmt->execute([
            $entityManager->flush();

            // Message utilisateur stoker en session
            // $_SESSION['msValidate'] = 'La catégorie a bien été enregistrée';
            // $_SESSION['success'] = 'La catégorie a bien été enregistrée';
            $this->addFlash('success', 'La catégorie a bien été enregistree');

            return $this->redirectToRoute('app_admin_category');

            // dump($request);
            // dump($category);

        }

        // Un classe Repository contient des methodes permettant uniquement d'executer des requetes de selections (SELECT) en BDD (find($id), findAll, findBy, findOneBy)

        $dbCategory = $repoCategory->findAll();
        // dump($dbCategory);

        return $this->render('admin/category.html.twig', [
            'categoryForm' => $form,
            'dbCategory' => $dbCategory
        ]);
    }

    #[Route('/admin/category/update/{id}', name: 'app_admin_category_update')]
    public function adminCategoryUpdate($id, Category $category, Request $request, EntityManagerInterface $entityManager, CategoryRepository $repoCategory): Response
    {




        $category = $repoCategory->find($id);
        // dump($id);
        // dump($category);

        $form = $this->createForm(CategoryFormType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // UPDATE category SET title = :title WHERE id = :id
            $entityManager->persist($category);
            $entityManager->flush();

            // dump($category->getTitle());
            $categoryTitle = $category->getTitle();

            $this->addFlash('success', "La catégorie <strong>$categoryTitle</strong> a été modifiée. ");

            return $this->redirectToRoute('app_admin_category');

        }

        $dbCategory = $repoCategory->findAll();
        dump($category->getProducts()->isEmpty());

        return $this->render('admin/category.html.twig', [
            'categoryForm' => $form,
            'dbCategory' => $dbCategory
        ]);
    }

    #[Route('/admin/category/remove/{id}', name: 'app_admin_category_remove')]
    public function adminCategoryRemove($id, EntityManagerInterface $entityManager, CategoryRepository $repoCategory)
    {

        $category = $repoCategory->find($id);
        dump($category);

        if ($category->getProducts()->isEmpty()) {
            //DELETE FROM category WHERE id = :id
            $entityManager->remove($category);
            $entityManager->flush();

            $this->addFlash('success', 'La catégorie a bien été supprimée');
        } else {
            $this->addFlash('danger', "Impossible de supprimer la categorie, des articles y sont associes.");
        }
        return $this->redirectToRoute('app_admin_category');
    }

    #[Route('/admin/orders', name: 'app_admin_orders')]
    public function adminOrders(): Response
    {
        return $this->render('admin/orders.html.twig', []);
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function adminUsers(): Response
    {
        return $this->render('admin/users.html.twig', []);
    }
}
