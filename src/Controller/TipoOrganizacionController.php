<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\TipoOrganizacion;

/**
 * TipoOrganizacion controller
 * @Route("/api",name="api_")
 */
class TipoOrganizacionController extends AbstractFOSRestController
{

  /**
   * name es solo para sermapeada en el archivo de config
  * @Route("/typesorg", name="serviceTypeOrg")
  */
  public function typesOrg()
  {
    
    $repository=$this->getDoctrine()->getRepository(TipoOrganizacion::class);
    $typesorg=$repository->findall();

    // hacemos el string serializable , controlamos las autoreferencias
    $typesorg = $this->get('serializer')->serialize($typesorg, 'json');
   
    $response = new Response($typesorg);
    $response->setStatusCode(Response::HTTP_OK);
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }
}