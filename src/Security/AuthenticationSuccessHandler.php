<?php

// src/Security/AuthenticationSuccessHandler.php
namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        /** @var UserInterface $user */
        $user = $token->getUser();

        // Générer le token JWT
        $jwt = $this->jwtManager->create($user);

        return new JsonResponse([
            'status' => true,
            'message' => 'Connexion réussie',
            'token' => $jwt,
            'roles' => $user->getRoles(),
        ]);
    }
}
