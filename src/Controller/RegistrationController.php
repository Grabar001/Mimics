<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        //   $_SESSION['user']
        /*
        public function getUser(){
            return $_SESSION['user'];
        }
        */
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        // dump($user);

        $form = $this->createForm(RegistrationFormType::class, $user);

        // $user->setEmail($_POST['email'])
        // $user->setFirstName($_POST['firstName'])
        $form->handleRequest($request);

        // if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {}
        if ($form->isSubmitted() && $form->isValid()) {

            //                      $_POST['password']
            $plainPassword = $form->get('password')->getData();
            $passwordHash = $userPasswordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($passwordHash);

            //              prepare("INSERT INTO user VALUES ($user->getFirstName())")
            $entityManager->persist($user);
            //            ->execute()
            $entityManager->flush();

            dump($passwordHash);
            dump($user);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form
        ]);
    }
}
