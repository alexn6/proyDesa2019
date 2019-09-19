<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Deporte;


 /**
 * Deporte controller
 * @Route("/api",name="api_")
 */
class DeporteController extends AbstractFOSRestController
{

  /**
   * name es solo para sermapeada en el archivo de config
  * @Route("/sports", name="serviceSport")
  */
  public function allSports()
  {

    $repository=$this->getDoctrine()->getRepository(Deporte::class);
    $sports=$repository->findall();

    $sports = $this->get('serializer')->serialize($sports, 'json', [
      'circular_reference_handler' => function ($object) {
        return $object->getId();
      }
    ]);

    $response = new Response($sports);
    $response->setStatusCode(Response::HTTP_OK);
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }
}