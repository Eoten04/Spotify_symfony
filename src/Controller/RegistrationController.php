<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_track_index');
        }

        $error = null;
        $success = false;

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');

            if (empty($email) || empty($password) || empty($passwordConfirm)) {
                $error = 'Tous les champs sont obligatoires.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Email invalide.';
            } elseif ($password !== $passwordConfirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } elseif (strlen($password) < 6) {
                $error = 'Le mot de passe doit contenir au moins 6 caractères.';
            } else {
                $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);

                if ($existingUser) {
                    $error = 'Un compte avec cet email existe déjà.';
                } else {
                    $user = new User();
                    $user->setEmail($email);
                    $hashedPassword = $passwordHasher->hashPassword($user, $password);
                    $user->setPassword($hashedPassword);

                    $em->persist($user);
                    $em->flush();

                    $success = true;
                }
            }
        }

        return $this->render('registration/register.html.twig', [
            'error' => $error,
            'success' => $success,
        ]);
    }
}