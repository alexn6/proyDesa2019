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

    /* $allTypes = array();
    
    $type1 = new TipoOrganizacion();
    $type1->setNombre = "Eliminatorias";
    $type1->setDescripcion = "Encuentro eliminatorios de un solo enfrentamiento";

    $type2 = new TipoOrganizacion();
    $type2->setNombre = "Liga Single";
    $type2->setDescripcion = "Competencia en la que se enfrentan todos los competidores contra todos donde el que se encuentra 1ro en la tabla es el ganador.";

    $type3 = new TipoOrganizacion();
    $type3->setNombre = "Liga Vuelta";
    $type3->setDescripcion = "Competencia en la que se enfrentan todos los competidores contra todos, dos rondas donde el que se encuentra 1ro en la tabla es el ganador."; */

    $typesJson = '[{"id":1,"nombre":"Eliminatorias","descripcion":"Encuentro eliminatorios de un solo enfrentamiento"},{"id":2,"nombre":"Liga Single","descripcion":"Competencia en la que se enfrentan todos los competidores contra todos donde el que se encuentra 1ro en la tabla es el ganador."},{"id":3,"nombre":"Liga Vuelta","descripcion":"Competencia en la que se enfrentan todos los competidores contra todos, dos rondas donde el que se encuentra 1ro en la tabla es el ganador."}]';
    
    // $someArray = json_decode($someJSON, true);
    /* $allTypes->add($type1);
    $allTypes->add($type2);
    $allTypes->add($type3); */

    /* array_push($allTypes,$type1);
    array_push($allTypes,$type2);
    array_push($allTypes,$type3); */

    // $allTypes = $this->get('serializer')->serialize($allTypes, 'json');

    $response = new Response($typesJson);
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }
}