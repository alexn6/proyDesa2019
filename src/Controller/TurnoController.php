<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use \Datetime;

use App\Entity\Turno;
use App\Entity\Competencia;

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

          // pasamos los datos a un array para poder trabajarlos
          $turnos = json_decode($turnos, true);

          // pasamos el dato de la a un formato mas simple
          for ($i=0; $i < count($turnos); $i++) {
            $turnos[$i]["hora_desde"] = substr($turnos[$i]["hora_desde"], 11, 5);
            $turnos[$i]['hora_hasta'] = substr($turnos[$i]['hora_hasta'], 11, 5);
          }
      }
      else{
          $turnos  = NULL;
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Faltan parametros";
      }

      $respJson = json_encode($turnos);
      
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
        $respJson = (object) null;
        $statusCode;

        // vemos si existe un body
        if(!empty($request->getContent())){
          // recuperamos los datos del body y pasamos a un array
          $dataTurnoRequest = json_decode($request->getContent());
          //var_dump($dataTurnoRequest);
          
          if(!$this->correctDataCreate($dataTurnoRequest)){
            $respJson->success = false;
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros o estan mal descriptos.";
          }
          else{
              // recuperamos los datos del body
              $idCompetencia = $dataTurnoRequest->idCompetencia;
              $hora_desde = $dataTurnoRequest->hs_desde;
              $hora_hasta = $dataTurnoRequest->hs_hasta;
              // pasar hora a Time
              $format = 'H:i:s';
              $hora_desde = DateTime::createFromFormat($format, $hora_desde);
              $hora_hasta = DateTime::createFromFormat($format, $hora_hasta);

              $repositoryCompetencia = $this->getDoctrine()->getRepository(Competencia::class);
              $competencia = $repositoryCompetencia->find($idCompetencia);
              
              if($this->correctTurno($competencia, $hora_desde, $hora_hasta)){
                // creamos el truno
                $newTurno = new Turno();
                $newTurno->setCompetencia($competencia);
                $newTurno->setHoraDesde($hora_desde);
                $newTurno->setHoraHasta($hora_hasta);
        
                $em = $this->getDoctrine()->getManager();
                $em->persist($newTurno);
                $em->flush();
        
                $statusCode = Response::HTTP_CREATED;
        
                $respJson->success = true;
                $respJson->messaging = "Creacion exitosa";
              }
              else{
                $respJson->success = false;
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Ya existe un turno similar. Verifique la diferencia horario entre turnos.";
              }          
          }

        }
        else{
          $respJson->success = false;
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Peticion mal formada. Faltan parametros o cuentan con nombres erroneos.";
        }
        
        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
      
        return $response;
    }

    // ######################################################################################
    // ############################ funciones auxiliares ####################################

    // controlamos que los datos recibidos esten completos
    private function correctDataCreate($dataRequest){
      if(!property_exists((object) $dataRequest,'idCompetencia')){
          var_dump("No existe el idComp");
          return false;
      }
      if(!property_exists((object) $dataRequest,'hs_desde')){
        var_dump("No existe el hs_desde");
        return false;
      }
      if(!property_exists((object) $dataRequest,'hs_hasta')){
        var_dump("No existe el hs_hasta");
        return false;
      }

    return true;
  }

  // controlamos que no exista un turno igual al q se quiere crear o cercano en hs y la
  // frecuencia de la competencia
  private function correctTurno($competencia, $hora_desde, $hora_hasta){
    $repositoryTurno = $this->getDoctrine()->getRepository(Turno::class);
    // controlamos que no haya un turno igual
    $turno = $repositoryTurno->findOneBy(['competencia' => $competencia, 'hora_desde' => $hora_desde, 'hora_hasta' => $hora_hasta]);
    if($turno != null){
      return false;
    }     
    $turno = $repositoryTurno->validarTurno($competencia, $hora_desde, $hora_hasta);

    if($turno != null){
      return false;
    }

    return true; 
  }

}