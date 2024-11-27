<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Repository\InscriptionRepository;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/inscriptions', name: 'api_inscriptions_')]
class ApiInscriptionController extends AbstractController
{
    // LISTE DES INSCRIPTIONS
    #[Route('/listeinscript', name: 'list', methods: ['GET'])]
    public function index(InscriptionRepository $inscriptionRepository): JsonResponse
    {
        /** @var UserInterface $user */
        $user = $this->getUser();

        $inscriptions = $user 
            ? $inscriptionRepository->findBy(['user' => $user]) 
            : $inscriptionRepository->findAll();

        return $this->json($inscriptions, 200, [], ['groups' => 'inscription:read']);
    }

    // AJOUTER UNE INSCRIPTION
    #[Route('/new', name: 'new', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        EvenementRepository $evenementRepository,
        ValidatorInterface $validator
    ): JsonResponse {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['status' => false, 'message' => 'Utilisateur non authentifié'], 401);
        }

        // Vérifier que l'utilisateur connecté a un email
        if (!$user->getEmail()) {
            return $this->json(['status' => false, 'message' => 'Email utilisateur non défini'], 400);
        }

        // Récupérer les données JSON
        $data = json_decode($request->getContent(), true);
        if (!isset($data['evenement_id'])) {
            return $this->json(['status' => false, 'message' => 'ID de l\'événement manquant'], 400);
        }

        // Récupérer l'événement
        $evenement = $evenementRepository->find($data['evenement_id']);
        if (!$evenement) {
            return $this->json(['status' => false, 'message' => 'Événement non trouvé'], 404);
        }

        // Créer une nouvelle inscription
        $inscription = new Inscription();
        $inscription->setUser($user);
        $inscription->setEmail($user->getEmail()); // Définir l'email de l'utilisateur connecté
        $inscription->setEvenement($evenement);

        // Définir la date d'inscription
        $dateInscri = isset($data['date_inscri']) ? new \DateTime($data['date_inscri']) : new \DateTime();
        $inscription->setDateInscri($dateInscri);

        // Valider l'entité
        $errors = $validator->validate($inscription);
        if (count($errors) > 0) {
            $validationErrors = [];
            foreach ($errors as $error) {
                $validationErrors[] = $error->getMessage();
            }
            return $this->json(['status' => false, 'errors' => $validationErrors], 400);
        }

        // Sauvegarder l'inscription
        $em->persist($inscription);
        $em->flush();

        return $this->json($inscription, 201, [], ['groups' => 'inscription:read']);
    }

    // DÉTAIL D'UNE INSCRIPTION
    #[Route('/inscrit/{id}', name: 'show', methods: ['GET'])]
    public function show(Inscription $inscription): JsonResponse
    {
        /** @var UserInterface $user */
        $user = $this->getUser();

        if ($user && $inscription->getUser() !== $user) {
            return $this->json(['status' => false, 'message' => 'Accès non autorisé'], 403);
        }

        return $this->json($inscription, 200, [], ['groups' => 'inscription:read']);
    }

    // METTRE À JOUR UNE INSCRIPTION
    #[Route('/editInscrit/{id}', name: 'edit', methods: ['PUT', 'PATCH'])]
    public function update(
        Request $request,
        Inscription $inscription,
        EntityManagerInterface $em,
        EvenementRepository $evenementRepository,
        ValidatorInterface $validator
    ): JsonResponse {
        /** @var UserInterface $user */
        $user = $this->getUser();

        if ($user && $inscription->getUser() !== $user) {
            return $this->json(['status' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['evenement_id'])) {
            $evenement = $evenementRepository->find($data['evenement_id']);
            if (!$evenement) {
                return $this->json(['status' => false, 'message' => 'Événement non trouvé'], 404);
            }
            $inscription->setEvenement($evenement);
        }

        if (isset($data['date_inscri'])) {
            try {
                $dateInscri = new \DateTime($data['date_inscri']);
                $inscription->setDateInscri($dateInscri);
            } catch (\Exception $e) {
                return $this->json(['status' => false, 'message' => 'Date invalide'], 400);
            }
        }

        $errors = $validator->validate($inscription);
        if (count($errors) > 0) {
            $validationErrors = [];
            foreach ($errors as $error) {
                $validationErrors[] = $error->getMessage();
            }
            return $this->json(['status' => false, 'errors' => $validationErrors], 400);
        }

        $em->flush();

        return $this->json($inscription, 200, [], ['groups' => 'inscription:read']);
    }

    // SUPPRIMER UNE INSCRIPTION
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Inscription $inscription, EntityManagerInterface $em): JsonResponse
    {
        /** @var UserInterface $user */
        $user = $this->getUser();

        if ($user && $inscription->getUser() !== $user) {
            return $this->json(['status' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $em->remove($inscription);
        $em->flush();

        return $this->json(['message' => 'Inscription supprimée avec succès.'], 200);
    }
}
