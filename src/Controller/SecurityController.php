<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

class SecurityController extends AbstractController
{
    #[Route("/api/login_check", name:"api_login_check", methods:"POST")]
    public function logiin(UserInterface $user, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        // Point de débogage pour vérifier l'authentification de l'utilisateur
        dd($user);

        // Génère le jeton JWT pour l'utilisateur authentifié
        $jwt = $jwtManager->create($user);
        $roles = $user->getRoles();

        // Vérifie si le jeton est vide (erreur de génération)
        if (empty($jwt)) {
            return new JsonResponse(
                ['status' => false, 'message' => 'Erreur de génération du jeton'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Retourne le jeton JWT dans la réponse JSON
        return new JsonResponse([
            'token' => $jwt,
            'status' => true,
            'roles' => $roles,
            'message' => 'Connexion réussie'
    ], Response::HTTP_OK);
    }
    


    #[Route('/api/user/{id}/roles', name: 'api_user_roles', methods: 'GET')]
    public function getUserRoles($id, UserRepository $userRepository): JsonResponse
    {
        // Vérifiez si l'ID est un entier
        if (!is_numeric($id)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid user ID'], 400);
        }
    
        $user = $userRepository->find((int)$id);
    
        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not found'], 404);
        }
    
        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }

}
