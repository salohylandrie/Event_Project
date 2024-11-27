<?php

namespace App\Controller;

use App\Entity\Evenement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Psr\Log\LoggerInterface;

class EvenementController extends AbstractController
{
    #[Route('/evenement', name: 'app_evenement', methods:['get'])]
    public function index(EntityManagerInterface $entityManagerInterface): JsonResponse
    {
       $evenements= $entityManagerInterface
        ->getRepository(Evenement::class)
        ->findAll();
        $data = [];

        foreach($evenements as $evenement){
            $data[]=[
                'id'=>$evenement->getId(),
                'titre_Even'=>$evenement->getTitreEven(),
                'descri_Even'=>$evenement->getDescriEven(),
                'date'=>$evenement->getDate()->format('d/m/Y H:i:s'),
                'lieu_Even'=>$evenement->getLieuEven(),
                'capacite_Even'=>$evenement->getCapaciteEven(),
                'categorie_Even'=>$evenement->getCategorieEven(),
            ];
        }
        return $this->json($data);
    }

    #[Route('/evenement/createEven', name: 'create_Even', methods: 'POST')]
    public function createEvent(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Décode les données JSON envoyées
        $data = json_decode($request->getContent(), true);
    
        // Si le JSON n'est pas valide ou vide
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON format or empty data.'], Response::HTTP_BAD_REQUEST);
        }
    
        // Définir les champs requis
        $requiredFields = ['titre_Even', 'descri_Even', 'date', 'lieu_Even', 'capacite_Even', 'categorie_Even'];
    
        // Vérification des champs requis
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->json([
                    'error' => "The field '$field' is required."
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    
        try {
            // Validation et conversion de la date
            $dateEven = $data['date'];
            $dateEvenObj = \DateTime::createFromFormat('d/m/Y H:i:s', $dateEven);
    
            // Si la conversion échoue
            if ($dateEvenObj === false) {
                return $this->json(['error' => 'Invalid date format. Please use d/m/Y H:i:s.'], Response::HTTP_BAD_REQUEST);
            }
    
            // Création de l'objet événement
            $evenement = new Evenement();
            $evenement->setTitreEven($data['titre_Even']);
            $evenement->setDescriEven($data['descri_Even']);
            $evenement->setDate($dateEvenObj);
            $evenement->setLieuEven($data['lieu_Even']);
            $evenement->setCapaciteEven((int)$data['capacite_Even']);
            $evenement->setCategorieEven($data['categorie_Even']);
    
            // Persister et sauvegarder en base de données
            $entityManager->persist($evenement);
            $entityManager->flush();
    
            // Réponse en cas de succès
            return $this->json([
                'id' => $evenement->getId(),
                'titre_Even' => $evenement->getTitreEven(),
                'descri_Even' => $evenement->getDescriEven(),
                'date' => $evenement->getDate()->format('d/m/Y H:i:s'),
                'lieu_Even' => $evenement->getLieuEven(),
                'capacite_Even' => $evenement->getCapaciteEven(),
                'categorie_Even' => $evenement->getCategorieEven(),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Gestion des erreurs générales
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    

  #[Route('/evenement/{id}', name:'evenement_show', methods:'GET')]
  public function show(EntityManagerInterface $entityManager,int $id): JsonResponse
  {
       $evenement= $entityManager->getRepository(Evenement::class)->find($id);
       if(!$evenement){
        return $this->json('Evenement not found'.$id, 404);
       }
       $data=[
        'id'=>$evenement->getId(),
        'titre_Even' => $evenement->getTitreEven(),
        'descri_Even' => $evenement->getDescriEven(),
        'date' => $evenement->getDate()->format('d/m/Y H:i:s'),
         'lieu_Even' => $evenement->getLieuEven(),
        'capacite_Even' => $evenement->getCapaciteEven(),
        'categorie_Even' => $evenement->getCategorieEven(),
       ];
       return $this->json($data);
  }

  #[Route('/evenements/{id}', name: 'Evenement_updata', methods: ['PUT', 'PATCH'])]
  public function update(EntityManagerInterface $entityManager, Request $request, int $id): JsonResponse
  {
      $evenement = $entityManager->getRepository(Evenement::class)->find($id);
  
      if (!$evenement) {
          return $this->json('Evenement not found with ID: ' . $id, 404);
      }
  
      // Récupération et validation des données
      $data = json_decode($request->getContent(), true);
  
      if (!$data) {
          return $this->json('Invalid JSON payload.', 400);
      }
  
      // Validation des champs
      $titreEven = $data['titre_Even'] ?? null;
      $descriEven = $data['descri_Even'] ?? null;
      $dateEven = $data['date'] ?? null;
      $lieuEven = $data['lieu_Even'] ?? null;
      $capaciteEven = $data['capacite_Even'] ?? null;
      $categorieEven = $data['categorie_Even'] ?? null;
  
      // Mise à jour des champs uniquement si les données sont présentes
      if ($titreEven !== null) {
          $evenement->setTitreEven($titreEven);
      }
      if ($descriEven !== null) {
          $evenement->setDescriEven($descriEven);
      }
      if ($dateEven !== null) {
          try {
              // Si la date est dans le format d/m/Y H:i:s (ex: 25/11/2030 06:32:03), convertissez-la
              $dateEvenObj = \DateTime::createFromFormat('d/m/Y H:i:s', $dateEven);
              if ($dateEvenObj === false) {
                  return $this->json('Invalid date format. Please use d/m/Y H:i:s.', 400);
              }
              $evenement->setDate($dateEvenObj);
          } catch (\Exception $e) {
              return $this->json('Invalid date format.', 400);
          }
      }
      if ($lieuEven !== null) {
          $evenement->setLieuEven($lieuEven);
      }
      if ($capaciteEven !== null) {
          $evenement->setCapaciteEven((int) $capaciteEven);
      }
      if ($categorieEven !== null) {
          $evenement->setCategorieEven($categorieEven);
      }
  
      $entityManager->flush();
  
      // Préparer les données de réponse
      $data = [
          'id' => $evenement->getId(),
          'titre_Even' => $evenement->getTitreEven(),
          'descri_Even' => $evenement->getDescriEven(),
          'date' => $evenement->getDate()->format('d/m/Y H:i:s'),
          'lieu_Even' => $evenement->getLieuEven(),
          'capacite_Even' => $evenement->getCapaciteEven(),
          'categorie_Even' => $evenement->getCategorieEven(),
      ];
  
      return $this->json($data);
  }
  


#[Route('/evenement/delete/{id}', name:'evenement_delete', methods:'DELETE')]
public function evenementDelet(EntityManagerInterface $entityManagerInterface, int $id) : JsonResponse {
    $evenement = $entityManagerInterface->getRepository(Evenement::class)->find($id);
    if(!$evenement){
        return $this->json('Evenement not found'.$id, 404);
    }
    $entityManagerInterface->remove($evenement);
    $entityManagerInterface->flush();
    return $this->json('Evenement delete successfully with'.$id);
}
}
