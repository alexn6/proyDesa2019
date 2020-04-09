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

use App\Utils\Constant;

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
    public function getTurnsByCompetition(Request $request){
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
              $hora_desde = DateTime::createFromFormat(Constant::FORMAT_DATE_HOUR, $hora_desde);
              $hora_hasta = DateTime::createFromFormat(Constant::FORMAT_DATE_HOUR, $hora_hasta);

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

    /**
     * Crea un conjunto de turnos.
     * @Rest\Post("/turn-set"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function createSet(Request $request){
      $respJson = (object) null;
      $statusCode;

      // vemos si existe un body
      if(!empty($request->getContent())){
        // recuperamos los datos del body y pasamos a un array
        $dataTurnoRequest = json_decode($request->getContent());
        
        if(!$this->correctDataCreate($dataTurnoRequest)){
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Peticion mal formada. Faltan parametros o estan mal descriptos.";
        }
        else{
            // recuperamos los datos del body
            $idCompetencia = $dataTurnoRequest->idCompetencia;
            $horaInicio = $dataTurnoRequest->horaInicio;
            $n_turnos = $dataTurnoRequest->cantidad;

            $duracion = $dataTurnoRequest->duracion;

            $repositoryCompetencia = $this->getDoctrine()->getRepository(Competencia::class);
            $competencia = $repositoryCompetencia->find($idCompetencia);

            if($competencia == null){
              $statusCode = Response::HTTP_BAD_REQUEST;
              $respJson->messaging = "La competencia no existe.";
            }
            else{
              // controlamos que no hayan turnos en el medio
              if($this->existTurn($competencia, $horaInicio, $duracion, $n_turnos)){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Existe turnos creados entre medio.";
              }
              else{
                $setTurns = $this->createSetTurn($competencia, $horaInicio, $duracion, $n_turnos);

                // persistimos los turnos
                $em = $this->getDoctrine()->getManager();
                for ($i=0; $i < count($setTurns); $i++) { 
                  $em->persist($setTurns[$i]);
                }
                $em->flush();

                $statusCode = Response::HTTP_CREATED;
                $respJson->messaging = "Creacion exitosa";
              }
            }
        }

      }
      else{
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Peticion mal formada. Faltan parametros o cuentan con nombres erroneos.";
      }
      
      $respJson = json_encode($respJson);

      $response = new Response($respJson);
      $response->headers->set('Content-Type', 'application/json');
      $response->setStatusCode($statusCode);
    
      return $response;
    }


    /**
     * Elimina un predio
     * @Rest\Delete("/del-turn"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function delete(Request $request){

      $respJson = (object) null;
      $statusCode;

      $idCompetencia = $request->get('idCompetencia');
      $idTurno = $request->get('idTurno');
    
      // vemos si recibimos el id de un predio para eliminarlo
      if(empty($idCompetencia)){
              $respJson->success = false;
              $statusCode = Response::HTTP_BAD_REQUEST;
              $respJson->messaging = "Peticion mal formada.";
      }else{
          if(empty($idTurno)){
              $respJson->success = false;
              $statusCode = Response::HTTP_BAD_REQUEST;
              $respJson->messaging = "Peticion mal formada. Faltan parametros.";
          }
          else{          
              $repository = $this->getDoctrine()->getRepository(Turno::class);
              $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
              
              $competencia = $repositoryComp->find($idCompetencia);
              $turno = $repository->find($idTurno);

              if(empty($competencia) || empty($turno)){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Competencia o turno no existe";
              }

              $turno = $repository->findOneBy(['competencia'=>$idCompetencia,'id'=>$idTurno]);
              if($turno == NULL){
                  $respJson->success = true;
                  $statusCode = Response::HTTP_OK;
                  $respJson->messaging = "El turno y/o competencia incorrecta o inexistente";
              }
              else{
                  // eliminamos el dato y refrescamos la DB
                  $em = $this->getDoctrine()->getManager();
                  $em->remove($turno);
                  $em->flush();
      
                  $respJson->success = true;
                  $statusCode = Response::HTTP_OK;
                  $respJson->messaging = "Eiminacion correcta";
              }
          }
      
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
      if(!property_exists((object) $dataRequest,'horaInicio')){
        var_dump("No existe la hora de inicio");
        return false;
      }
      if(!property_exists((object) $dataRequest,'cantidad')){
        var_dump("No existe la cantidad");
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

  // controlamos que no exista un turno entre el futuro conjunto de turnos a crear
  private function existTurn($competencia, $horaInicio, $duracion, $n_turnos){
    $horaIniSet = DateTime::createFromFormat(Constant::FORMAT_DATE_HOUR, $horaInicio);
    $horaFinSet = clone $horaIniSet;
    // vamos con el fin del turno
    $duracion = $duracion*$n_turnos;
    $horaFinSet->modify("+{$duracion} minutes");

    $hrIniSet = $horaIniSet->format(Constant::FORMAT_DATE_HOUR);
    $hrFinSet = $horaFinSet->format(Constant::FORMAT_DATE_HOUR);

    $repository = $this->getDoctrine()->getRepository(Turno::class);
    if($repository->findTurnInitBetween($competencia->getId(), $hrIniSet, $hrFinSet) != null){
      //var_dump("Encuentra turno");
      return true;
    }
    if($repository->findTurnEndBetween($competencia->getId(), $hrIniSet, $hrFinSet) != null){
      //var_dump("Encuentro turno");
      return true;
    }
    //var_dump("No hay turnos.");
    return false;
  }

  // crea un conjunto de turno
  private function createSetTurn($competencia, $horaInicio, $duracion, $n){
    $setTurns = array();
    $horaInicio = DateTime::createFromFormat(Constant::FORMAT_DATE_HOUR, $horaInicio);

    for ($i=0; $i < $n; $i++) {
      $hora_desde = clone $horaInicio;
      $hora_hasta = clone $horaInicio;

      // vamos con el fin del turno
      $hora_hasta->modify("+{$duracion} minutes");

      //creamos el turno
      $newTurno = new Turno();
      $newTurno->setCompetencia($competencia);
      $newTurno->setHoraDesde($hora_desde);
      $newTurno->setHoraHasta($hora_hasta);

      // agregamos los turnos a un array
      array_push($setTurns, $newTurno);

      // seteamos el inicio del nuevo turno
      $horaInicio = clone $hora_hasta;
    }

    return $setTurns;
  }

}