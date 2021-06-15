<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login", methods={"GET", "POST"})
     */
    public function loginAction(AuthenticationUtils $authenticationUtils): Response
    {
        // Handle fails only
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('main/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/register", name="register", methods={"GET", "POST"})
     */
    public function registerAction(Request $request,
                                   UserPasswordEncoderInterface $passwordEncoder,
                                   EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('main/register.html.twig');
        }

        $name = $request->get('_name', '');
        $email = $request->get('_username', '');
        $password = $request->get('_password', '');
        $repeatPassword = $request->get('_repeatPassword', '');

        if (empty($name) ||
            empty($email) ||
            empty($password) ||
            empty($repeatPassword))
        {
            return $this->render('main/register.html.twig', [
                'error' => 'Не все поля заполнены'
            ]);
        }

        if ($password !== $repeatPassword) {
            return $this->render('main/register.html.twig', [
                'error' => 'Пароли не совпадают'
            ]);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user) {
            return $this->render('main/register.html.twig', [
                'error' => 'Email занят'
            ]);
        }

        $user = new User();
        $user
            ->setName($name)
            ->setEmail($email)
            ->setPassword($passwordEncoder->encodePassword($user, $password));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirect('/login');
    }

    /**
     * @Route("/logout", name="logout", methods={"GET"})
     */
    public function logoutAction(): void
    {
        // Handles with Symfony
    }
}