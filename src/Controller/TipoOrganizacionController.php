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

    $typesJson = '[{"id":1,"nombre":"Eliminatorias","descripcion":"Encuentro eliminatorios de un solo enfrentamiento"},{"id":2,"nombre":"Liga Single","descripcion":"Competencia en la que se enfrentan todos los competidores contra todos donde el que se encuentra 1ro en la tabla es el ganador."},{"id":3,"nombre":"Liga Vuelta","descripcion":"Competencia en la que se enfrentan todos los competidores contra todos, dos rondas donde el que se encuentra 1ro en la tabla es el ganador."}]';
   
    $response = new Response($typesJson);
    $response->setStatusCode(Response::HTTP_OK);
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }
}