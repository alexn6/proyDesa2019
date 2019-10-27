<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Encuentro;

use App\Utils\Constant;

/**
 * TipoOrganizacion controller
 * @Route("/api",name="api_")
 */
class EncuentroController extends AbstractFOSRestController
{

  /**
   * Creamos y persistimos un objeto del tipo Encuentro
  * @Route("/confrontation")
  */
  public function save()
  {
    
    // $repository=$this->getDoctrine()->getRepository(TipoOrganizacion::class);
    // $typesorg=$repository->findall();

    // // hacemos el string serializable , controlamos las autoreferencias
    // $typesorg = $this->get('serializer')->serialize($typesorg, 'json');
   
    // $response = new Response($typesorg);
    // $response->setStatusCode(Response::HTTP_OK);
    // $response->headers->set('Content-Type', 'application/json');

    // return $response;
    return null;
  }

  public function saveFixture($matches, $competencia, $tipoorg){
      // analizamos si la competencia es con grupos o no
    if($tipoorg == Constant::COD_TIPO_ELIMINATORIAS){

    }
    if($tipoorg == Constant::COD_TIPO_LIGA_SINGLE){

    }
    if($tipoorg == Constant::COD_TIPO_LIGA_DOUBLE){

    }
    if($tipoorg == Constant::COD_TIPO_ELIMINATORIAS_DOUBLE){

    }
    if($tipoorg == Constant::COD_TIPO_FASE_GRUPOS){

    }
  }

  // ################################################################
  // ################ funciones privadas ############################

  private function saveEliminatorias($encuentros, $competencia){
    // recuperamos el id y la fase de a copetencia
  }
  
  private function saveEncuentro($idComp1, $idComp2, $tipoorg){

  }
}