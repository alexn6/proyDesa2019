<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Turno;

/**
 * Categorias controller
 * @Route("/api",name="api_")
 */
class TurnoController extends AbstractFOSRestController
{


    /**
     * Devuelve todas los predios de una competencia
     * @Rest\Get("/turn/competition"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getGroundsByCompetition(Request $request){
      $repository=$this->getDoctrine()->getRepository(Turno::class);
    
      $respJson = (object) null;

      $idCompetencia = $request->get('idCompetencia');
      $idTurno = null;
      // vemos si recibimos algun parametro
      if(!empty($idCompetencia)){
          $turnos = $repository->findTurnByCompetetition($idCompetencia,$idTurno);
          $statusCode = Response::HTTP_OK;

          $turnos = $this->get('serializer')->serialize($turnos, 'json', [
              'circular_reference_handler' => function ($object) {
                  return $object->getId();
              },
              'ignored_attributes' => ['competencia']
          ]);
      }
      else{
          $turnos  = NULL;
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Faltan parametros";
      }

      $respJson = json_decode($turnos);
      $respJson = json_encode($respJson);
      
      $response = new Response($respJson);
      $response->setStatusCode(Response::HTTP_OK);
      $response->headers->set('Content-Type', 'application/json');
  
      return $response;
  }


    /**
     * Crea un predio.
     * @Rest\Post("/turn"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function create(Request $request){
      
        return $response;
    }

    // ######################################################################################
    // ############################ funciones auxiliares ####################################

    // controlamos que los datos recibidos esten completos
    private function correctDataCreate($dataRequest){
      if(!property_exists((object) $dataRequest,'idCompetencia')){
          return false;
      }
      if(!property_exists((object) $dataRequest,'hora_desde')){
        return false;

      }
      if(!property_exists((object) $dataRequest,'hora_hasta')){
        return false;
      }

    return true;
  }

  // controlamos que no exista un turno igual al q se quiere crear o cercano en hs y la
  // frecuencia de la competencia
  private function correctTurno($idCompetencia, $hora_desde, $hora_hasta){
    $repositoryTurno = $this->getDoctrine()->getRepository(Turno::class);
    // controlamos que no haya un turno igual
    $turno = $repositoryTurno->findOneBy(['competencia' => $idCompetencia, 'hora_desde' => $hora_desde, 'hora_hasta' => $hora_hasta]);
    if($turno != null){
      return false;
    }     
    $turno = $repositoryTurno->validarTurno($idCompetencia, $hora_desde, $hora_hasta);
     return true; 
    }

}