<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use \Datetime;

use App\Entity\Encuentro;
use App\Entity\Usuario;
use App\Entity\UsuarioCompetencia;
use App\Entity\Competencia;
use App\Entity\Jornada;
use App\Entity\Campo;
use App\Entity\Turno;
use App\Entity\Juez;
use App\Entity\Rol;
use App\Entity\Resultado;

use App\Controller\JornadaController;

use App\Utils\Constant;

/**
 * TipoOrganizacion controller
 * @Route("/api",name="api_")
 */
class EncuentroController extends AbstractFOSRestController
{

  const GANADOR_COMPETIDOR1 = 1;
  const EMPATE = 0;
  const GANADOR_COMPETIDOR2 = -1;

  /**
   * Editamos los datos de los Encuentros
  * @Rest\Put("/confrontation")
  */
  public function edit(Request $request)
  {
    $respJson = (object) null;
    $statusCode;

    // vemos si existe un body
    if(!empty($request->getContent())){
      // recuperamos los datos del body y pasamos a un array
      $dataRequest = json_decode($request->getContent());
      
      // en el caso de no recibir datos le asginamos un null para mantener
      if(!property_exists((object) $dataRequest,'idCompetencia') || !property_exists((object) $dataRequest,'idEncuentro')){
        $respJson->msg = "La competencia y/o encuentro son obligatorios";
        $statusCode = Response::HTTP_BAD_REQUEST;
      }
      else{        
        $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
        $competencia = $repositoryComp->find($dataRequest->idCompetencia);

        // recuperamos el encuentro 
        $repositoryEnc = $this->getDoctrine()->getRepository(Encuentro::class);
        $encuentro = $repositoryEnc->findOneBy(['id'=> $dataRequest->idEncuentro, 'competencia'=> $competencia]);

        $turno;
        $campo;
        $juez;
        $hayCamposActualizados = false;
        $reciboResultados = false;

        // ###########################################################################################
        // ############ controlamos que la asignacion de campo, juez y turno sea correcta ############
        // si no hay turno entonces se realiza la asignacion sin ningun control
        if((!property_exists((object) $dataRequest,'idTurno')) && ($encuentro->getTurno() == null)){
          // si existe agregamos el juez
          if(property_exists((object) $dataRequest,'idJuez')){
            $repositoryJuez = $this->getDoctrine()->getRepository(Juez::class);
            $juez = $repositoryJuez->find($dataRequest->idJuez);
            $encuentro->setJuez($juez);
          }
          // si existe agregamos el campo
          if(property_exists((object) $dataRequest,'idCampo')){
            $repositoryCampo = $this->getDoctrine()->getRepository(Campo::class);
            $campo = $repositoryCampo->find($dataRequest->idCampo);
            $encuentro->setCampo($campo);
          }
        }
        else{
          // recuperamos el valor del turno
          if(property_exists((object) $dataRequest,'idTurno')){
            $repositoryTurno = $this->getDoctrine()->getRepository(Turno::class);
            $turno = $repositoryTurno->find($dataRequest->idTurno);
          }
          else{
            $turno = $encuentro->getTurno();
          }
          // si no existe un campo debemos controlar solo el juez, si existe
          if((!property_exists((object) $dataRequest,'idCampo')) && ($encuentro->getCampo() == null)){
            // si tmpoco tenemos juez entonces solo seteamos el turno
            if((!property_exists((object) $dataRequest,'idJuez')) && ($encuentro->getJuez() == null)){
              if(property_exists((object) $dataRequest,'idTurno')){
                // solo seteamos el campo si lo recibimos desde la peticion
                $encuentro->setTurno($turno);
                $hayCamposActualizados = true;
              }
            }
            // si existe un juez, ya sea de la peticion o ya almacenado
            else{
              // recuperamos el valor del juez
              if(property_exists((object) $dataRequest,'idJuez')){
                $repositoryJuez = $this->getDoctrine()->getRepository(Juez::class);
                $juez = $repositoryJuez->find($dataRequest->idJuez);
              }
              else{
                $juez = $encuentro->getJuez();
              }
              // con los datos del juez y turno, pasamos a controlar que sean correctos
              if($this->availableJudge($dataRequest->idEncuentro, $competencia, $juez, $turno)){
                if(property_exists((object) $dataRequest,'idTurno')){
                  $encuentro->setTurno($turno);
                }
                if(property_exists((object) $dataRequest,'idJuez')){
                  $encuentro->setJuez($juez);
                }
                $hayCamposActualizados = true;
              }
              else{
                $respJson->msg = "El juez no esta disponible en el turno del encuentro";
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson = json_encode($respJson);

                $response = new Response($respJson);
                $response->headers->set('Content-Type', 'application/json');
                $response->setStatusCode($statusCode);
                
                return $response;
              }
            }
          }
          // en el caso de que haya campo, ya sea de la peticion o el del objeto persistido
          else{
            // recuperamos el valor del campo
            if(property_exists((object) $dataRequest,'idCampo')){
              // var_dump("Reconoce el idCampo");
              $repositoryCampo = $this->getDoctrine()->getRepository(Campo::class);
              $campo = $repositoryCampo->find($dataRequest->idCampo);
            }
            else{
              $campo = $encuentro->getCampo();
              //var_dump("recupera campo de la DB");
            }
            // controlamos que el campo este disponible
            if($this->availableField($dataRequest->idEncuentro, $competencia, $campo, $turno)){
              // var_dump("El campo esta disponible");
              if(property_exists((object) $dataRequest,'idTurno')){
                $encuentro->setTurno($turno);
              }
              if(property_exists((object) $dataRequest,'idCampo')){
                $encuentro->setCampo($campo);
              }
              $hayCamposActualizados = true;
            }
            else{
              $respJson->msg = "El campo no esta disponible en el turno del encuentro";
              $statusCode = Response::HTTP_BAD_REQUEST;
              $respJson = json_encode($respJson);

              $response = new Response($respJson);
              $response->headers->set('Content-Type', 'application/json');
              $response->setStatusCode($statusCode);
              
              return $response;
            }

            // recuperamos el valor del juez
            if(property_exists((object) $dataRequest,'idJuez')){
              $repositoryJuez = $this->getDoctrine()->getRepository(Juez::class);
              $juez = $repositoryJuez->find($dataRequest->idJuez);
            }
            else{
              $juez = $encuentro->getJuez();
            }
            // con los datos del juez y turno, pasamos a controlar que sean correctos
            if($this->availableJudge($dataRequest->idEncuentro, $competencia, $juez, $turno)){
              if(property_exists((object) $dataRequest,'idTurno')){
                $encuentro->setTurno($turno);
              }
              if(property_exists((object) $dataRequest,'idJuez')){
                $encuentro->setJuez($juez);
              }
              $hayCamposActualizados = true;
            }
            else{
              $respJson->msg = "El juez no esta disponible en el turno del encuentro";
              $statusCode = Response::HTTP_BAD_REQUEST;
              $respJson = json_encode($respJson);

              $response = new Response($respJson);
              $response->headers->set('Content-Type', 'application/json');
              $response->setStatusCode($statusCode);
              
              return $response;
            }
          }
        }

        if((property_exists((object) $dataRequest,'rdo_comp1')) || property_exists((object) $dataRequest,'rdo_comp2')){
          $reciboResultados = true;
        }

        if($reciboResultados){
          $rdoDBEncuentroComp1 = $encuentro->getRdoComp1();
          $rdoDBEncuentroComp2 = $encuentro->getRdoComp2();
          // vemos si el encuentro ya contenia resultados
          if(($rdoDBEncuentroComp1 != NULL) || ($rdoDBEncuentroComp2 != NULL)){
            // var_dump("editamos el resultado existente");
            // TODO: actualizar(-) con encuentro DB
            $this->reverseUpdateResult($encuentro, $encuentro->getRdoComp1(), $encuentro->getRdoComp2());
            // TODO: actualizar(+) con datos recibidos
            $this->updateResult($encuentro, $dataRequest->rdo_comp1, $dataRequest->rdo_comp2);
          }
          else{
            // var_dump("Agregamos un resultado");
            // actualizamos los partidos jugados y los PG, PE, PP, con los datos recibidos
            $this->updateResult($encuentro, $dataRequest->rdo_comp1, $dataRequest->rdo_comp2);
            $this->updateJugadosCompetitors($encuentro);
          }
          // asigno los resultados al encuentro
          $encuentro->setRdoComp1($dataRequest->rdo_comp1);
          $encuentro->setRdoComp2($dataRequest->rdo_comp2);
          $hayCamposActualizados = true;
        }

        if($hayCamposActualizados){
          $respJson->msg = "Campos actualizados correctamente";
          $statusCode = Response::HTTP_OK;
          $em = $this->getDoctrine()->getManager();
          $em->flush();
        }
      }
    }
    else{
      $respJson->msg = "Peticion mal formada";
      $statusCode = Response::HTTP_BAD_REQUEST;
    }
    
    $respJson = json_encode($respJson);

    $response = new Response($respJson);
    $response->headers->set('Content-Type', 'application/json');
    $response->setStatusCode($statusCode);
    
    return $response;
  }

  /**
   * Editamos los datos de los Encuentros
  * @Rest\Put("/confrontation-off")
  */
  public function editResultsOff(Request $request)
  {
    $respJson = (object) null;
    $statusCode;

    // vemos si existe un body
    if(!empty($request->getContent())){
      // recuperamos los datos del body y pasamos a un array
      $dataRequest = json_decode($request->getContent());
      // $dataRequest = $request->getContent();
      
      if(!property_exists((object) $dataRequest,'encuentros')){
        $respJson->msg = "No hay encuentros por actualizar.";
        $statusCode = Response::HTTP_BAD_REQUEST;
      }
      else{
        $repositoryEnc = $this->getDoctrine()->getRepository(Encuentro::class);
        $encuentros = $dataRequest->encuentros;
         // pasamos lo recibido a un array
        $encuentros = json_decode($encuentros);

        // actualizamos todos los encuentros
        for ($i=0; $i < count($encuentros); $i++) { 
          $encuentroRequest = $encuentros[$i];
          $idEncuentro = $encuentroRequest->idEncuentro;
          
          $encuentroDb = $repositoryEnc->findOneBy(['id'=> $idEncuentro]);

          $em = $this->getDoctrine()->getManager();
          if($encuentroDb != NULL){
            $encuentroDb->setRdoComp1($encuentroRequest->rdo_comp1);
            $encuentroDb->setRdoComp2($encuentroRequest->rdo_comp2);
          }
          $em->flush();
        }

        $respJson->msg = "Encuentros actualizados.";
        $statusCode = Response::HTTP_OK;
      }
    }
    else{
      $respJson->msg = "Peticion mal formada";
      $statusCode = Response::HTTP_BAD_REQUEST;
    }
    
    $respJson = json_encode($respJson);

    $response = new Response($respJson);
    $response->headers->set('Content-Type', 'application/json');
    $response->setStatusCode($statusCode);
    
    return $response;
  }

  /**
     * 
     * @Rest\Get("/confrontations")
     * Por nombre de competencia
     * 
     * @return Response
     */
    public function generateMatchesCompetition(Request $request){
      $idCompetition = $request->get('idCompetencia');

      $respJson = (object) null;
      $statusCode;
     
      // vemos si recibimos algun parametro
      if(!empty($idCompetition)){
          $repository = $this->getDoctrine()->getRepository(Competencia::class);
          $competition = $repository->find($idCompetition);

          if(empty($competition)){
              $respJson->matches = NULL;
              $statusCode = Response::HTTP_BAD_REQUEST;
              $respJson->msg = "La competencia no existe o fue eliminada";
          }
          else{
            // recuperamos los encuentros de una competencia
            $repositoryEnc = $this->getDoctrine()->getRepository(Encuentro::class);
            $respJson = $repositoryEnc->findEncuentrosByCompetencia($idCompetition);

            $statusCode = Response::HTTP_OK;
          }
      }
      else{
        $respJson->encuentros = NULL;
        $respJson->msg = "Solicitud mal formada";
        $statusCode = Response::HTTP_BAD_REQUEST;
      }

      $respJson = json_encode($respJson);

      $response = new Response($respJson);
      $response->setStatusCode($statusCode);
      $response->headers->set('Content-Type', 'application/json');

      return $response;
    }

    /**
     * 
     * @Rest\Post("/confrontations/competition")
     * Recupera los encuentros de la competencia por fecha y grupo
     * 
     * @return Response
     */
    public function getConfratationsByCompetitionMin(Request $request){
      $idCompetition = $request->get('idCompetencia');

      $respJson = (object) null;
      $statusCode;
     
      // vemos si recibimos algun parametro
      if(!empty($idCompetition)){
          // controlamos que exista la competencia
          $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
          $competition = $repositoryComp->find($idCompetition);

          if(empty($competition)){
              $respJson->matches = NULL;
              $statusCode = Response::HTTP_BAD_REQUEST;
              $respJson->msg = "La competencia no existe o fue eliminada";
              $respJson = json_encode($respJson);
          }
          else{
            // vemos si existe un body
            if(!empty($request->getContent())){
                $fase = null;
                $jornada = null;
                $grupo = null;
                // recuperamos los datos del body y pasamos a un array
                $dataConfrontationRequest = json_decode($request->getContent());

                // ->
                $encuentros;
                // vemos si es una LIGA
                if(strpos($competition->getOrganizacion()->getNombre(), 'Liga') !== false ){
                  $jornada = $dataConfrontationRequest->jornada;
                  if(property_exists((object) $dataConfrontationRequest,'fase')){
                    $fase = $dataConfrontationRequest->fase;
                  }
                  $encuentros = $this->getEncuentrosLiga($idCompetition, $jornada, $fase);
                }
                // vemos si es una ELIMINATORIA
                if(strpos($competition->getOrganizacion()->getNombre(), 'Eliminatoria') !== false ){
                  $fase = $dataConfrontationRequest->fase;
                  if(property_exists((object) $dataConfrontationRequest,'jornada')){
                    $jornada = $dataConfrontationRequest->jornada;
                  }
                  $encuentros = $this->getEncuentrosEliminatoria($idCompetition, $fase, $jornada);
                }
                // si es fase grupos
                if(strpos($competition->getOrganizacion()->getNombre(), 'grupo') !== false ){
                  $fase = $dataConfrontationRequest->fase;
                  if(property_exists((object) $dataConfrontationRequest,'jornada')){
                    $jornada = $dataConfrontationRequest->jornada;
                  }
                  if(property_exists((object) $dataConfrontationRequest,'grupo')){
                    $grupo = $dataConfrontationRequest->grupo;
                  }
                  $encuentros = $this->getEncuentrosGrupos($idCompetition, $fase, $grupo, $jornada);
                }
                
                // mostramos solo el alias de los competidores
                for ($i=0; $i < count($encuentros); $i++) {
                    $encuentros[$i]['competidor1'] = $encuentros[$i]['competidor1']['alias'];
                    $encuentros[$i]['competidor2'] = $encuentros[$i]['competidor2']['alias'];
                }
    
                // harcode de los resultados con null
                for ($i=0; $i < count($encuentros); $i++) {
                  if($encuentros[$i]['rdoComp1'] === null){
                    $encuentros[$i]['rdoComp1'] = -1;
                  }
                  if($encuentros[$i]['rdoComp2'] === null){
                    $encuentros[$i]['rdoComp2'] = -1;
                  }
                  // si hy turno
                  if($encuentros[$i]['turno'] !== null){
                    $encuentros[$i]['turno']['horaDesde'] = substr($encuentros[$i]['turno']['horaDesde'], -14, 5);
                    $encuentros[$i]['turno']['horaHasta'] = substr($encuentros[$i]['turno']['horaHasta'], -14, 5);
                  }
                }
                // <-
                // *******> Recuperamos el competidor libre de la competencia, si es que lo hay
                // recuperamos los alias de los competidores de los encuentros
                $aliasCompetitorsEnc = $this->getAliasCompetitors($encuentros);
                // var_dump($aliasCompetitorsEnc);
                // recuperamos los alias de todos los competidores de la liga
                if(strpos($competition->getOrganizacion()->getNombre(), 'Liga') !== false ){
                  $aliasCompetitorsDb = $repositoryComp->getAliasCompetitors($competition->getId());
                }
                if(strpos($competition->getOrganizacion()->getNombre(), 'grupo') !== false ){
                  $aliasCompetitorsDb = $repositoryComp->getAliasCompetitorsByGroup($competition->getId(), $dataConfrontationRequest->grupo);
                }
                // var_dump($aliasCompetitorsEnc);
                // var_dump($aliasCompetitorsDb);
                $compLibre = array_diff($aliasCompetitorsDb, $aliasCompetitorsEnc);
                $libre = null;
                foreach ($compLibre as $valor){
                  $libre = $valor;
                }
                //var_dump($compLibre);
                // *******< 
                $statusCode = Response::HTTP_OK;
                $respJson->encuentros = $encuentros;
                $respJson->libre = $libre;
            }
            else{
              $respJson->encuentros = NULL;
              $respJson->msg = "Solicitud mal formada. Faltan parametros";
              $respJson = json_encode($respJson);
              $statusCode = Response::HTTP_BAD_REQUEST;
            }
          }
      }
      else{
        $respJson->encuentros = NULL;
        $respJson->msg = "Solicitud mal formada";
        $respJson = json_encode($respJson);
        $statusCode = Response::HTTP_BAD_REQUEST;
      }

      $respJson = json_encode($respJson);
      $response = new Response($respJson);
      $response->setStatusCode($statusCode);
      $response->headers->set('Content-Type', 'application/json');

      return $response;
    }

    /**
     * 
     * @Rest\Get("/confrontations/competition-off")
     * Devuelve los encuentros de la comptencia para trabajar de manera offline
     * 
     * @return Response
     */
    public function getConfratationsByCompetitionOffline(Request $request){
      $idCompetition = $request->get('idCompetencia');

      $respJson = (object) null;
      $statusCode;
     
      // vemos si recibimos algun parametro
      if(!empty($idCompetition)){
          // controlamos que exista la competencia
          $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
          $competition = $repositoryComp->find($idCompetition);

          if(empty($competition)){
              $respJson->matches = NULL;
              $statusCode = Response::HTTP_BAD_REQUEST;
              $respJson->msg = "La competencia no existe o fue eliminada";
          }
          else{
            $repositoryEnc = $this->getDoctrine()->getRepository(Encuentro::class);

            // recuperamos todos los encuentros de la competencia
            $encuentros = $repositoryEnc->findEncuentrosByCompetencia($idCompetition);
            $encuentros = $this->get('serializer')->serialize($encuentros, 'json', [
              'circular_reference_handler' => function ($object) {
                return $object->getId();
              },
              'ignored_attributes' => ['competencia', 'roles', '__initializer__','__cloner__', '__isInitialized__']
            ]);
            // pasamos los datos a un array para poder trabajarlos
            $encuentros = json_decode($encuentros, true);
            
            // mostramos solo el alias de los competidores
            for ($i=0; $i < count($encuentros); $i++) {
                $encuentros[$i]['competidor1'] = $encuentros[$i]['competidor1']['alias'];
                $encuentros[$i]['competidor2'] = $encuentros[$i]['competidor2']['alias'];
            }

            // harcode de los resultados con null
            for ($i=0; $i < count($encuentros); $i++) {
              if($encuentros[$i]['rdoComp1'] === null){
                $encuentros[$i]['rdoComp1'] = -1;
              }
              if($encuentros[$i]['rdoComp2'] === null){
                $encuentros[$i]['rdoComp2'] = -1;
              }
            }

            // modificamos la jornada y agregams su fase
            for ($i=0; $i < count($encuentros); $i++) {
              // le agregamos la jornada y fase
              $jornada = $encuentros[$i]['jornada'];
              $encuentros[$i]['jornada'] = $jornada['numero'];
              $encuentros[$i]['fase'] = $jornada['fase'];
              // le agregamos el id del juez
              $juez = $encuentros[$i]['juez'];
              $encuentros[$i]['juez'] = $juez['id'];
              // le agregamos el id del campo
              $campo = $encuentros[$i]['campo'];
              $encuentros[$i]['campo'] = $campo['id'];
              
              $turno = $encuentros[$i]['turno'];
              $hDesde= substr($turno['horaDesde'], -14, 5);
              $hHasta= substr($turno['horaHasta'], -14, 5);
              $encuentros[$i]['turno'] = $hDesde." - ".$hHasta;
              $fecha = substr($turno['horaDesde'], 0, -15);
              if(!$fecha){
                $fecha = " - ";
              }
              $encuentros[$i]['fecha'] = $fecha;
              // le agregamos el id de la competencia a cada encuentro
              $encuentros[$i]['idCompetencia'] = $idCompetition;
            }
            $encuentros = json_encode($encuentros);

            $statusCode = Response::HTTP_OK;
            $respJson = $encuentros;
          }
      }
      else{
        $respJson->encuentros = NULL;
        $respJson->msg = "Solicitud mal formada";
        $statusCode = Response::HTTP_BAD_REQUEST;
      }

      $response = new Response($respJson);
      $response->setStatusCode($statusCode);
      $response->headers->set('Content-Type', 'application/json');

      return $response;
    }

    // para ignorar atributos dentro de una entidad a la hora de serializar
    //https://symfony.com/doc/current/components/serializer.html#serializing-an-object

  // ########################################################################
  // ########### deberia llamarse a la hora de generar encuentros ############

  public function saveFixture($matches, $competencia, $tipoorg){
    // var_dump($tipoorg);
      // analizamos si la competencia es con grupos o no
    if(($tipoorg == Constant::COD_TIPO_ELIMINATORIAS) || ($tipoorg == Constant::COD_TIPO_ELIMINATORIAS_DOUBLE)){
      $this->saveEliminatorias($matches, $competencia);
    }
    if(($tipoorg == Constant::COD_TIPO_LIGA_SINGLE) || ($tipoorg == Constant::COD_TIPO_LIGA_DOUBLE)){
      // recorremos el array, cada i da la joranada(fecha) y pasa la lista
      // de encuentros a la funcion de guardar encuentros
      $this->saveLiga($matches, $competencia);
    }
    if($tipoorg == Constant::COD_TIPO_FASE_GRUPOS){
      $this->saveGrupos($matches, $competencia);
    }
  }

  // persiste los encuentros de una nueva fase
  public function saveFixtureNewPhase($matches, $competencia){
    // le vamos agregando la frecuencia de juego de la competencia a la fecha de inicio
    $repositoryJornada = $this->getDoctrine()->getRepository(Jornada::class);
    // recuperamos la fecha de la ultima jornada, para usarla como referencia para la nueva fase
    $lastedJornada = $repositoryJornada->lastedByCompetition($competencia->getId());
    $stringFecha = $lastedJornada[0][1];
    // var_dump($fechaJornada);

    // vamos guardando los encuentros
    for ($i=1; $i <= count($matches); $i++) {
      $jornada = $i;
      // le vamos agregando la frecuencia de juego de la competencia a la fecha de inicio
      $diasFrec = $competencia->getFrecDias()*$i;
      $fechaJornada = Date(Constant::FORMAT_DATE_CREATE, strtotime($stringFecha. ' + '.$diasFrec.' days'));
      $this->saveEncuentrosCompetition($matches[$i], $competencia, $jornada, null, $fechaJornada);
    }
  }

  // ################################################################
  // ################ funciones privadas ############################

  // gurda en la DB los encuentros generados en una eliminatorio(single y double)
  private function saveEliminatorias($fixtureEncuentros, $competencia){
    $frec_jornada = $competencia->getFrecDias();
    // recuperamos el id y la fase de la competencia
    for ($i=1; $i <= count($fixtureEncuentros); $i++) {
      $jornada = $i;
      // le vamos agregando la frecuencia de juego de la competencia a la fecha de inicio
      $dias_frec = $frec_jornada*($i-1);
      $fecha_jornada = date(Constant::FORMAT_DATE_CREATE, strtotime($competencia->getFechaIni()->format(Constant::FORMAT_DATE). ' + '.$dias_frec.' days'));
      // ######### CORREGIR #########
      $this->saveEncuentrosCompetition($fixtureEncuentros[$i], $competencia, $jornada, null, $fecha_jornada);
    }
  }

  // guardamos los encuentros generados en una liga (single y double)
  private function saveLiga($fixtureEncuentros, $competencia){
    $frec_jornada = $competencia->getFrecDias();
    // recorremos la lista de encuentros y persistimos los encuentros
    for ($i=1; $i <= count($fixtureEncuentros); $i++) {
      $jornada = $i;
      // le vamos agregando la frecuencia de juego de la competencia a la fecha de inicio
      $dias_frec = $frec_jornada*($i-1);
      $fecha_jornada = date(Constant::FORMAT_DATE_CREATE, strtotime($competencia->getFechaIni()->format(Constant::FORMAT_DATE). ' + '.$dias_frec.' days'));
      $this->saveEncuentrosCompetition($fixtureEncuentros[$i], $competencia, $jornada, null, $fecha_jornada);
    }
  }

  // guardamos los encuentros generados por una competencia con grupos
  private function saveGrupos($fixtureEncuentros, $competencia){
    $frec_jornada = $competencia->getFrecDias();
    // el fixture serian los matches, controlar que tengan cant de grupos
    for ($i=0; $i < count($fixtureEncuentros); $i++) {
      $fixtureGrupo = $fixtureEncuentros[$i]["Encuentros"];
      $grupo = $i+1;
      for ($j=1; $j <= count($fixtureGrupo); $j++) {
        $jornada = $j;
        // le vamos agregando la frecuencia de juego de la competencia a la fecha de inicio
        $dias_frec = $frec_jornada*($j-1);
        $fecha_jornada = date(Constant::FORMAT_DATE_CREATE, strtotime($competencia->getFechaIni()->format(Constant::FORMAT_DATE). ' + '.$dias_frec.' days'));
        $this->saveEncuentrosCompetition($fixtureGrupo[$j], $competencia, $jornada, $grupo, $fecha_jornada);
      }
    }
  }


  // solo recibiriamos la lista de encuentros
  // persistimos los encuentros de la competencia
  private function saveEncuentrosCompetition($encuentros, $competencia, $jornada, $grupo, $fecha_jornada){
    $repository = $this->getDoctrine()->getRepository(Encuentro::class);
    $repositoryUserComp = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
    $em = $this->getDoctrine()->getManager();
    // recorremos todos los encuentros
    for ($i=0; $i < count($encuentros); $i++) {
      $nameComp1 = $encuentros[$i][0];
      $nameComp2 = $encuentros[$i][1];
      // no persistimos encuentros con competiodres libres
      if(($nameComp1 != NULL) && ($nameComp2 != NULL)){
        // vamos a recuperar los competidores del encuentro
        $competitor1 = $repositoryUserComp->findOneBy(['alias' => $nameComp1]);
        $competitor2 = $repositoryUserComp->findOneBy(['alias' => $nameComp2]);
        // recuperamos la jornada que corresponde
        $newJornada = $this->getJornada($jornada, $competencia, $fecha_jornada);
        // creamos el encuentro
        $newEncuentro = new Encuentro();
        $newEncuentro->setCompetencia($competencia);
        $newEncuentro->setJornada($newJornada);
        $newEncuentro->setCompetidor1($competitor1);
        $newEncuentro->setCompetidor2($competitor2);
  
        if($grupo != NULL){
          $newEncuentro->setGrupo($grupo);
        }
  
        $em->persist($newEncuentro);
      }
    }

    $em->flush();
  }
  
  // recupera la jornada de un encuentro segun el nro de jornada
  private function getJornada($jornada, $competencia, $fecha){
    $repository = $this->getDoctrine()->getRepository(Jornada::class);

    $jornadaEncuentro = $repository->findOneBy(['numero' => $jornada, 'competencia' => $competencia, 'fase' =>$competencia->getFaseActual()]);

    // vemos si existe la jornada
    if($jornadaEncuentro == NULL){
      $fecha_date = DateTime::createFromFormat(Constant::FORMAT_DATE_CREATE, $fecha);
      // si no existe la creamos y la guardamos
      $jornadaEncuentro = new Jornada();
      $jornadaEncuentro->setCompetencia($competencia);
      $jornadaEncuentro->setNumero($jornada);
      $jornadaEncuentro->setFecha($fecha_date);
      $jornadaEncuentro->setFase($competencia->getFaseActual());

      $this->forward('App\Controller\JornadaController::save', [
        'newJornada'  => $jornadaEncuentro
      ]);
    }

    return $jornadaEncuentro;
  }

  // controlamos si existe un encuentro con el mismo juez y en el mismo turno
  private function availableJudge($idEncuentro, $competencia, $juez, $turno){
    $repository = $this->getDoctrine()->getRepository(Encuentro::class);
    $encuentro = $repository->findOneBy(['competencia' => $competencia, 'turno' => $turno, 'juez' => $juez]);
    if($encuentro == null){
      return true;
    }
    else{
      if($idEncuentro == $encuentro->getId()){
        return true;
      }
    }

    return false;
  }

  // controlamos si existe un encuentro con el mismo campo y en el mismo turno
  private function availableField($idEncuentro, $competencia, $campo, $turno){
    $repository = $this->getDoctrine()->getRepository(Encuentro::class);
    $encuentro = $repository->findOneBy(['competencia' => $competencia, 'turno' => $turno, 'campo' => $campo]);
    if($encuentro == null){
      return true;
    }
    else{
      if($idEncuentro == $encuentro->getId()){
        return true;
      }
    }

    return false;
  }

  // devolvemos un array con los alias de todos los competidores de los encuentros
  private function getAliasCompetitors($encuentros){
    $aliasCompetitors = array();
    for ($i=0; $i < count($encuentros); $i++) {
      array_push($aliasCompetitors, $encuentros[$i]['competidor1']);
      array_push($aliasCompetitors, $encuentros[$i]['competidor2']);
    }
    return $aliasCompetitors;
  }

  // #############################################################################
  // ###################### recuperacion de encuentros ######################

  // recuperamos los encuentros de una liga con los parametros recibidos
  private function getEncuentrosLiga($idCompetencia, $jornada, $fase){
    $repositoryEnc = $this->getDoctrine()->getRepository(Encuentro::class);
    $newEncuentros = $repositoryEnc->findEncuentrosLiga($idCompetencia, $jornada);
    $newEncuentros = $this->get('serializer')->serialize($newEncuentros, 'json', [
      'circular_reference_handler' => function ($object) {
        return $object->getId();
      },
      'ignored_attributes' => ['competencia', 'roles', '__initializer__','__cloner__', '__isInitialized__']
    ]);
    // pasamos los datos a un array para poder trabajarlos
    $newEncuentros = json_decode($newEncuentros, true);

    return $newEncuentros;
  }

  // recuperamos los encuentros de una eliminatoria con los parametros recibidos
  private function getEncuentrosEliminatoria($idCompetition, $fase, $jornada){
    $repositoryEnc = $this->getDoctrine()->getRepository(Encuentro::class);
    $encuentros = $repositoryEnc->findEncuentrosEliminatoria($idCompetition, $fase, $jornada);
    $encuentros = $this->get('serializer')->serialize($encuentros, 'json', [
      'circular_reference_handler' => function ($object) {
        return $object->getId();
      },
      'ignored_attributes' => ['competencia', 'roles', '__initializer__','__cloner__', '__isInitialized__']
    ]);
    // pasamos los datos a un array para poder trabajarlos
    $encuentros = json_decode($encuentros, true);
    
    return $encuentros;
  }

  private function getEncuentrosGrupos($idCompetition, $fase, $grupo, $jornada){
    $repositoryEnc = $this->getDoctrine()->getRepository(Encuentro::class);
    $encuentros;
    // vemos si no estamos en fase de grupos => es ELIMINATORIA
    if($fase != 0){
      $encuentros = $repositoryEnc->findEncuentrosEliminatoria($idCompetition, $fase, $jornada);
    }
    // si estamos en fase de grupos
    else{
      $encuentros = $repositoryEnc->findEncuentrosFaseGrupos($idCompetition, $jornada, $grupo);
    }
    $encuentros = $this->get('serializer')->serialize($encuentros, 'json', [
      'circular_reference_handler' => function ($object) {
        return $object->getId();
      },
      'ignored_attributes' => ['competencia', 'roles', '__initializer__','__cloner__', '__isInitialized__']
    ]);
    $encuentros = json_decode($encuentros, true);

    return $encuentros;
  }

  // #############################################################################
  // #############################################################################

  // segun el resultado(ya creado) del encuentro actualizamos los campos de resultado de cada uno
  private function updateResult($encuentro, $rdoComp1, $rdoComp2){
    $entityCreated = false;
    // calculo el resultado
    $rdoEncuentro = $this->resolvedResultConfrontation($rdoComp1, $rdoComp2);
    
    $repositoryUSerComp = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
    $userComp1 = $repositoryUSerComp->find($encuentro->getCompetidor1()->getId());
    $userComp2 = $repositoryUSerComp->find($encuentro->getCompetidor2()->getId());
    // TODO: incorporar el caso de que no exista un resultado (CREARLO)
    $repository = $this->getDoctrine()->getRepository(Resultado::class);
    $resultCompetidor1 = $repository->findOneBy(['competidor' => $userComp1]);
    $resultCompetidor2 = $repository->findOneBy(['competidor' => $userComp2]);

    // si los resultados de las competencias no existen, los creamos
    if($resultCompetidor1 == NULL){
      $resultCompetidor1 = $this->createResult($userComp1);
      $entityCreated = true;
    }
    if($resultCompetidor2 == NULL){
      $resultCompetidor2 = $this->createResult($userComp2);
      $entityCreated = true;
    }

    // buscamos los datos de resultado de cada competidor
    // TODO: tener en cuenta q al principio esta en NULL
    if($rdoEncuentro === $this::GANADOR_COMPETIDOR1){
      $resultCompetidor1->setGanados($resultCompetidor1->getGanados() + 1);
      $resultCompetidor2->setPerdidos($resultCompetidor2->getPerdidos() + 1);
    }
    if($rdoEncuentro === $this::GANADOR_COMPETIDOR2){
      $resultCompetidor2->setGanados($resultCompetidor2->getGanados() + 1);
      $resultCompetidor1->setPerdidos($resultCompetidor1->getPerdidos() + 1);
    }
    if($rdoEncuentro === $this::EMPATE){
      $resultCompetidor1->setEmpatados($resultCompetidor1->getEmpatados() + 1);
      $resultCompetidor2->setEmpatados($resultCompetidor2->getEmpatados() + 1);
    }

    if($entityCreated){
      $em = $this->getDoctrine()->getManager();
      $em->persist($resultCompetidor1);
      $em->persist($resultCompetidor2);
      $em->flush();
    }
  }

  // segun el resultado(datos recibidos en la peticion) del encuentro actualizamos los campos de resultado de cada uno
  private function reverseUpdateResult($encuentro, $rdoComp1, $rdoComp2){
    // calculo el resultado
    $rdoEncuentro = $this->resolvedResultConfrontation($rdoComp1, $rdoComp2);
    
    $repositoryUSerComp = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
    $userComp1 = $repositoryUSerComp->find($encuentro->getCompetidor1()->getId());
    $userComp2 = $repositoryUSerComp->find($encuentro->getCompetidor2()->getId());

    $repository = $this->getDoctrine()->getRepository(Resultado::class);
    $resultCompetidor1 = $repository->findOneBy(['competidor' => $userComp1]);
    $resultCompetidor2 = $repository->findOneBy(['competidor' => $userComp2]);

    // buscamos los datos de resultado de cada competidor
    // TODO: tener en cuenta q al principio esta en NULL
    if($rdoEncuentro === $this::GANADOR_COMPETIDOR1){
      $resultCompetidor1->setGanados($resultCompetidor1->getGanados() - 1);
      $resultCompetidor2->setPerdidos($resultCompetidor2->getPerdidos() - 1);
    }
    if($rdoEncuentro === $this::GANADOR_COMPETIDOR2){
      $resultCompetidor2->setGanados($resultCompetidor2->getGanados() - 1);
      $resultCompetidor1->setPerdidos($resultCompetidor1->getPerdidos() - 1);
    }
    if($rdoEncuentro === $this::EMPATE){
      $resultCompetidor1->setEmpatados($resultCompetidor1->getEmpatados() - 1);
      $resultCompetidor2->setEmpatados($resultCompetidor2->getEmpatados() - 1);
    }

    $em = $this->getDoctrine()->getManager();
    $em->persist($resultCompetidor1);
    $em->persist($resultCompetidor2);
    $em->flush();
  }

  // actualizamos los PJ de cada competidor del encuentro
  private function updateJugadosCompetitors($encuentro){
    
    $repositoryUSerComp = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
    $userComp1 = $repositoryUSerComp->find($encuentro->getCompetidor1()->getId());
    $userComp2 = $repositoryUSerComp->find($encuentro->getCompetidor2()->getId());
    $repository = $this->getDoctrine()->getRepository(Resultado::class);
    $resultCompetidor1 = $repository->findOneBy(['competidor' => $userComp1]);
    $resultCompetidor2 = $repository->findOneBy(['competidor' => $userComp2]);

    // buscamos los datos de resultado de cada competidor
    // TODO: tener en cuenta q al principio esta en NULL
    $resultCompetidor1->setJugados($resultCompetidor1->getJugados() + 1);
    $resultCompetidor2->setJugados($resultCompetidor2->getJugados() + 1);
  }

  // determina el resultado de un encuentro
  private function resolvedResultConfrontation($rdoComp1, $rdoComp2){
    return $rdoComp1 <=> $rdoComp2;
  }

  // crea y devuelve un objeto Resultado
  private function createResult($competidor){
    $resultado = new Resultado();
    $resultado->setCompetidor($competidor);
    $resultado->setJugados(0);
    $resultado->setGanados(0);
    $resultado->setEmpatados(0);
    $resultado->setPerdidos(0);

    return $resultado;
  }
}