<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    private $manager;
    private $userRepository;
    private $passwordHasher;
   
    public function __construct(
        EntityManagerInterface $manager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
    
    ) {
        $this->manager = $manager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
     
    }

    #[Route('/userCreate', name: 'user_create', methods: 'POST')]
    public function userCreate(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $password = $data['password'];

        $email_exist = $this->userRepository->findOneByEmail($email);
        if ($email_exist) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Cet email existe déjà, veuillez le changer',
            ]);
        }

        $user = new User();
        $user->setEmail($email);

        // Hachage du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse([
            'status' => true,
            'message' => 'Utilisateur créé avec succès',
        ]);
    }

    #[Route("/api/login_check", name:"api_login_check")]
    public function login(Request $request, JWTTokenManagerInterface $jwtManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        // Vérifiez si la requête est reçue
        error_log("Login endpoint hit");
    
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
    
        if (!$email || !$password) {
            error_log("Email ou mot de passe manquant");
            return new JsonResponse([
                'status' => false,
                'message' => 'Email et mot de passe sont requis'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        // Vérification utilisateur
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            error_log("Utilisateur introuvable");
            return new JsonResponse([
                'status' => false,
                'message' => 'Identifiants invalides'
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }
    
        // Vérification du mot de passe
        if (!$passwordHasher->isPasswordValid($user, $password)) {
            error_log("Mot de passe invalide");
            return new JsonResponse([
                'status' => false,
                'message' => 'Identifiants invalides'
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }
    
        // Génération du token
        $token = $jwtManager->create($user);
        error_log("Token généré : $token");
    
        return new JsonResponse([
            'status' => true,
            'token' => $token,
            'roles' => $user->getRoles(),
            'message' => 'Connexion réussie'
        ]);
    }
    
    


    #[Route('/getAllUser', name: 'get_All_user', methods: 'GET')]
    public function getAllUsers(Request $request): Response
    {
        $users = $this->userRepository->findAll();
        return $this->json($users, 200);
    }
}