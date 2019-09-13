<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Usuario;

class testControllerSPT extends AbstractController
{


  /**
   * name es solo para sermapeada en el archivo de config
  * @Route("/api/testUsers", name="testService")
  */
  public function respTestSrvTorneos()
  {

    $repository=$this->getDoctrine()->getRepository(Usuario::class);
    $users=$repository->findall();

    // hacemos el string serializable
    $users = $this->get('serializer')->serialize($users, 'json');

    $response = new Response($users);
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }
}