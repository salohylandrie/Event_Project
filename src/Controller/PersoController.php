<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersoController extends AbstractController
{
   #[Route("/lucky/texteht")]
public function texte(): Response
{
    return $this->render('lucky/texteht.html.twig');
}
}
