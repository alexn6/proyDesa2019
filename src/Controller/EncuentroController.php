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
              $repositoryCampo = $this->getDoctrine()->getRepository(Campo::class);
              $campo = $repositoryCampo->find($dataRequest->idCampo);
            }
            else{
              $campo = $encuentro->getCampo();
              //var_dump("recupera campo de la DB");
            }
            // controlamos que el campo este disponible
            if($this->availableField($dataRequest->idEncuentro, $competencia, $campo, $turno)){
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

        // editamos los campos que corresponda
        // if(property_exists((object) $dataRequest,'rdo_comp1')){
        //   $encuentro->setRdoComp1($dataRequest->rdo_comp1);
        //   $hayCamposActualizados = true;
        // }
        // if(property_exists((object) $dataRequest,'rdo_comp2')){
        //   $encuentro->setRdoComp2($dataRequest->rdo_comp2);
        //   $hayCamposActualizados = true;
        // }
        if((property_exists((object) $dataRequest,'rdo_comp1')) || property_exists((object) $dataRequest,'rdo_comp2')){
          $reciboResultados = true;
        }

        if($reciboResultados){
          $rdoDBEncuentroComp1 = $encuentro->getRdoComp1();
          $rdoDBEncuentroComp2 = $encuentro->getRdoComp2();
          // vemos si el encuentro ya contenia resultados
          if(($rdoDBEncuentroComp1 != NULL) || ($rdoDBEncuentroComp2 != NULL)){
            // TODO: actualizar(-) con encuentro DB
            $this->reverseUpdateResult($encuentro, $encuentro->getRdoComp1(), $encuentro->getRdoComp2());
            // TODO: actualizar(+) con datos recibidos
            $this->updateResult($encuentro, $dataRequest->rdo_comp1, $dataRequest->rdo_comp2);
          }
          else{
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
     * Por nombre de competencia
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
          }
          else{
            $repositoryEnc = $this->getDoctrine()->getRepository(Encuentro::class);
            $fase = null;
            $grupo = null;
            $idJornada = null;
            // vemos si existe un body
            if(!empty($request->getContent())){
                //var_dump("body vacio");
                // recuperamos los datos del body y pasamos a un array
                $dataConfrontationRequest = json_decode($request->getContent());

                $hayFase = property_exists((object) $dataConfrontationRequest,'fase');
                if($hayFase){
                  $fase = $dataConfrontationRequest->fase;
                  // ################################ NO BORRAR #################################
                  // buscamos el id correspondiente a la fase
                  $repositoryJornada = $this->getDoctrine()->getRepository(Jornada::class);
                  $idJornada = $repositoryJornada->findOneBy(['competencia'=> $competition, 'numero'=> $fase])->getId();
                  //  
                }
                $hayGrupo = property_exists((object) $dataConfrontationRequest,'grupo');
                if($hayGrupo){
                  $grupo = $dataConfrontationRequest->grupo;
                }
            }

            // recuperamos inicialmente los datos del competidor1
            $encuentros = $repositoryEnc->findEncuentrosComp1ByCompetencia($idCompetition, $idJornada, $grupo);
            $encuentros = $this->get('serializer')->serialize($encuentros, 'json', [
              'circular_reference_handler' => function ($object) {
                return $object->getId();
              },
              'ignored_attributes' => ['usuarioscompetencias', 'grupo', 'competencia', 'pass', 'token', 'password', 'roles', 'salt', 'username', 'jornada', '__initializer__','__cloner__', '__isInitialized__']
            ]);
            // pasamos los datos a un array para poder trabajarlos
            $encuentros = json_decode($encuentros, true);

            // recuperamos los datos del competidor2
            $encuentrosComp2 = $repositoryEnc->findEncuentrosComp2ByCompetencia($idCompetition, $idJornada, $grupo);
            $encuentrosComp2 = $this->get('serializer')->serialize($encuentrosComp2, 'json', [
              'circular_reference_handler' => function ($object) {
                return $object->getId();
              },
              'ignored_attributes' => ['usuarioscompetencias', 'grupo', 'competencia', 'pass', 'token', 'password', 'roles', 'salt', 'username', 'jornada', '__initializer__','__cloner__', '__isInitialized__']
            ]);
            // pasamos los datos a un array para poder trabajarlos
            $encuentrosComp2 = json_decode($encuentrosComp2, true);

            // creamos un nuevo array con los datos que necesitamos
            $encuentrosFull = array();
            // guardamos los encuentros
            for ($i=0; $i < count($encuentros); $i++) { 
              array_push($encuentrosFull, $encuentros[$i][0]);
            }
            
            // harcode de los resultados con null
            for ($i=0; $i < count($encuentrosFull); $i++) {
              if($encuentrosFull[$i]['rdoComp1'] === null){
                $encuentrosFull[$i]['rdoComp1'] = -1;
              }
              if($encuentrosFull[$i]['rdoComp2'] === null){
                $encuentrosFull[$i]['rdoComp2'] = -1;
              }
            }

            // colocamos los alias de los competidores
            for ($i=0; $i < count($encuentrosFull); $i++) { 
              $aliasComp1 = $encuentros[$i]['competidor1'];
              $aliasComp2 = $encuentrosComp2[$i]['competidor2'];
              $encuentrosFull[$i]['competidor1'] = $aliasComp1;
              $encuentrosFull[$i]['competidor2'] = $aliasComp2;
            }

            $encuentrosFull = $this->get('serializer')->serialize($encuentrosFull, 'json');

            $statusCode = Response::HTTP_OK;
            $respJson = $encuentrosFull;
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

  // ################################################################
  // ################ funciones privadas ############################

  // gurda en la DB los encuentros generados en una eliminatorio(single y double)
  private function saveEliminatorias($fixtureEncuentros, $competencia){
    // var_dump(count($fixtureEncuentros));
    $frec_jornada = 6;
    //$frec_jornada = $competencia->getFrecuencia();
    // recuperamos el id y la fase de a competencia
    for ($i=1; $i <= count($fixtureEncuentros); $i++) {
      $jornada = $i;
      var_dump($fixtureEncuentros[$i]);
      //var_dump($fixtureEncuentros[0]);
      // le vamos agregando la frecuencia de juego de la competencia a la fecha de inicio
      $dias_frec = $frec_jornada*($i-1);
      $fecha_jornada = date('Y-m-d', strtotime($competencia->getFechaIni()->format('Y-m-d'). ' + '.$dias_frec.' days'));
      //var_dump($fixtureEncuentros[$i]);
      // ######### CORREGIR #########
      // var_dump($fixtureEncuentros[0]);
      $this->saveEncuentrosCompetition($fixtureEncuentros[$i], $competencia, $jornada, null, $fecha_jornada);
    }
  }

  // guardamos los encuentros generados en una liga (single y double)
  private function saveLiga($fixtureEncuentros, $competencia){
    $frec_jornada = 6;
    //$frec_jornada = $competencia->getFrecuencia();
    // recuperamos el id y la fase de a copetencia
    // recorremos la lista de encuentros y persistimos los encuentros
    for ($i=1; $i <= count($fixtureEncuentros); $i++) {
      $jornada = $i;
      // le vamos agregando la frecuencia de juego de la competencia a la fecha de inicio
      $dias_frec = $frec_jornada*($i-1);
      $fecha_jornada = date('Y-m-d', strtotime($competencia->getFechaIni()->format('Y-m-d'). ' + '.$dias_frec.' days'));
      $this->saveEncuentrosCompetition($fixtureEncuentros[$i], $competencia, $jornada, null, $fecha_jornada);
    }
  }

  // guardamos los encuentros generados por una competencia con grupos
  private function saveGrupos($fixtureEncuentros, $competencia){
    // var_dump("Entro a saveGrupos()");
    $frec_jornada = 6;
    //$frec_jornada = $competencia->getFrecuencia();
    // el fixture serian los matches, controlar que tengan cant de grupos
    for ($i=0; $i < count($fixtureEncuentros); $i++) {
      $fixtureGrupo = $fixtureEncuentros[$i]["Encuentros"];
      $grupo = $i+1;
      for ($j=1; $j <= count($fixtureGrupo); $j++) {
        $jornada = $j;
        // le vamos agregando la frecuencia de juego de la competencia a la fecha de inicio
        $dias_frec = $frec_jornada*($j-1);
        $fecha_jornada = date('Y-m-d', strtotime($competencia->getFechaIni()->format('Y-m-d'). ' + '.$dias_frec.' days'));
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
    var_dump($encuentros);
    //var_dump($competencia->getId());
    // recorremos todos los encuentros
    for ($i=0; $i < count($encuentros); $i++) {
      $nameComp1 = $encuentros[$i][0];
      $nameComp2 = $encuentros[$i][1];
      // var_dump($nameComp1);
      // var_dump($nameComp2);
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

    $em->flush();
  }
  
  // recupera la jornada de un encuentro segun el nro de jornada
  private function getJornada($jornada, $competencia, $fecha){
    $repository = $this->getDoctrine()->getRepository(Jornada::class);

    // TODO: agregar nueva query (incluir fase, la fase actual de la competencia)
    $jornadaEncuentro = $repository->findOneBy(['numero' => $jornada, 'competencia' => $competencia]);

    // vemos si existe la jornada
    if($jornadaEncuentro == NULL){
      $formato = 'Y-m-d';
      $fecha_date = DateTime::createFromFormat($formato, $fecha);
      // si no existe la creamos y la guardamos
      $jornadaEncuentro = new Jornada();
      $jornadaEncuentro->setCompetencia($competencia);
      $jornadaEncuentro->setNumero($jornada);
      $jornadaEncuentro->setFecha($fecha_date);
      // TODO: cambiar por getFaseActual()
      $jornadaEncuentro->setFase($competencia->getFase());

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