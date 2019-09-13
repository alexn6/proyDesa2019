<?php

namespace App\Controller;

//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Usuario;

 /**
 * Usuario controller
 * @Route("/api",name="api_")
 */
class UsuarioController extends AbstractFOSRestController
{
  /**
   * name es solo para sermapeada en el archivo de config
  * @Route("/api/users", name="serviceUser")
  */
  public function allUsers()
  {
    $repository=$this->getDoctrine()->getRepository(Usuario::class);
    $users=$repository->findall();

    // hacemos el string serializable
    $users = $this->get('serializer')->serialize($users, 'json');

    // $response = new Response($users);
    $response = new Response("algo de users");
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }

  /**
     * Lista de todos los deportes.
     * @Rest\Get("/all-users"), defaults={"_format"="json"})
     * 
     * @return Response
     */
  public function getUsers(){
    $repository=$this->getDoctrine()->getRepository(Usuario::class);
    $users=$repository->findall();


    // hacemos el string serializable , controlamos las autoreferencias
    $users = $this->get('serializer')->serialize($users, 'json');

    $response = new Response($users);
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }
}