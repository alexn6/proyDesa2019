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
use App\Entity\Competencia;
use App\Entity\Jornada;

use App\Controller\JornadaController;

use App\Utils\Constant;

/**
 * TipoOrganizacion controller
 * @Route("/api",name="api_")
 */
class EncuentroController extends AbstractFOSRestController
{

  /**
   * Creamos y persistimos un objeto del tipo Encuentro
  * @Rest\Get("/confrontation")
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
            $repositoryEnc = $this->getDoctrine()->getRepository(Encuentro::class);
            // recuperamos los usuario_competencia de una competencia
            $respJson = $repositoryEnc->findEncuentrosByCompetencia($idCompetition);

            $statusCode = Response::HTTP_OK;
            //$respJson->msg = "Operacion exitosa";
             //= $encuentros;
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
              'ignored_attributes' => ['usuarioscompetencias', 'competencia', 'pass', 'token', 'password', 'roles', 'salt', 'username', 'jornada', '__initializer__','__cloner__', '__isInitialized__']
            ]);
            // pasamos los datos a un array para poder trabajarlos
            $encuentros = json_decode($encuentros, true);

            // recuperamos los datos del competidor2
            $encuentrosComp2 = $repositoryEnc->findEncuentrosComp2ByCompetencia($idCompetition, $idJornada, $grupo);
            $encuentrosComp2 = $this->get('serializer')->serialize($encuentrosComp2, 'json', [
              'circular_reference_handler' => function ($object) {
                return $object->getId();
              },
              'ignored_attributes' => ['usuarioscompetencias', 'competencia', 'pass', 'token', 'password', 'roles', 'salt', 'username', 'jornada', '__initializer__','__cloner__', '__isInitialized__']
            ]);
            // pasamos los datos a un array para poder trabajarlos
            $encuentrosComp2 = json_decode($encuentrosComp2, true);

            // creamos un nuevo array con los datos que necesitamos
            $encuentrosFull = array();
            // guardamos los encuentros
            for ($i=0; $i < count($encuentros); $i++) { 
              array_push($encuentrosFull, $encuentros[$i][0]);
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
  

  // /**
  //  * Creamos y persistimos un objeto del tipo Encuentro
  // * @Rest\Get("/saveLiga")
  // */
  // public function saveFixtureLiga()
  // {
  //   $fechas = array();
  //   $fecha1 = array();
  //   $fecha2 = array();
    
  //   array_push($fecha1, ["alex6", "sergiov"]);
  //   array_push($fecha1, ["lucasa", "algo_t"]);
  //   array_push($fecha2, ["alexMovil", "Seguidor"]);
  //   array_push($fecha2, ["Organizador", "Participante"]);
  //   //print_r($fecha1); 
  //   array_push($fechas, $fecha1);
  //   array_push($fechas, $fecha2);

  //   $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
  //   $competencia = $repositoryComp->find(7);

  //   $this->saveLiga($fechas, $competencia);

  //   $response = new Response();
  //   $response->setStatusCode(Response::HTTP_OK);
  //   $response->headers->set('Content-Type', 'application/json');

  //   return $response;
  // }

  // /** solo de PRUEBA
  //  * Creamos y persistimos un objeto del tipo Encuentro
  // * @Rest\Get("/saveElim")
  // */
  // public function saveFixtureEliminatorias()
  // {
  //   $fechas = array();
  //   $fechaCuartos = array();
    
  //   array_push($fechaCuartos, ["alex6", "sergiov"]);
  //   array_push($fechaCuartos, ["lucasa", "algo_t"]);
  //   array_push($fechaCuartos, ["alexMovil", "Seguidor"]);
  //   array_push($fechaCuartos, ["Organizador", "Participante"]);
  //   //print_r($fecha1); 
  //   array_push($fechas, $fechaCuartos);

  //   $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
  //   $competencia = $repositoryComp->find(7);

  //   $this->saveEliminatorias($fechas, $competencia);

  //   $response = new Response();
  //   $response->setStatusCode(Response::HTTP_OK);
  //   $response->headers->set('Content-Type', 'application/json');

  //   return $response;
  // }

  // /** solo de PRUEBA
  //  * Creamos y persistimos un objeto del tipo Encuentro
  // * @Rest\Get("/saveElimdoub")
  // */
  // public function saveFixtureEliminatoriasDoubles()
  // {
  //   $fechas = array();
  //   $fechaSemiIda = array();
  //   $fechaSemiVuelta = array();
    
  //   array_push($fechaSemiIda, ["alexMovil", "Seguidor"]);
  //   array_push($fechaSemiIda, ["Organizador", "Participante"]);
  //   array_push($fechaSemiVuelta, ["Seguidor", "alexMovil"]);
  //   array_push($fechaSemiVuelta, ["Participante", "Organizador"]);
  //   //print_r($fecha1); 
  //   array_push($fechas, $fechaSemiIda);
  //   array_push($fechas, $fechaSemiVuelta);

  //   $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
  //   // asignamos una competencia con tipo LigaDouble
  //   $competencia = $repositoryComp->find(4);

  //   $this->saveEliminatorias($fechas, $competencia);

  //   $response = new Response();
  //   $response->setStatusCode(Response::HTTP_OK);
  //   $response->headers->set('Content-Type', 'application/json');

  //   return $response;
  // }

  // /** solo de PRUEBA
  //  * Creamos y persistimos un objeto del tipo Encuentro
  // * @Rest\Get("/saveGrup")
  // */
  // public function saveFixtureGrupos()
  // {
  //   $fechas = array();
  //   $grupo1 = array();
  //   $fechaG1 = array();
  //   $fechaG2 = array();
    
  //   array_push($fechaG1, ["alexMovil", "Seguidor"]);
  //   array_push($fechaG1, ["Organizador", "Participante"]);
  //   array_push($fechaG2, ["Seguidor", "Organizador"]);
  //   array_push($fechaG2, ["Participante", "alexMovil"]);
  //   //print_r($fecha1); 
  //   array_push($grupo1, $fechaG1);
  //   array_push($grupo1, $fechaG2);

  //   $grupo2 = array();
  //   $fecha2G1 = array();
  //   $fecha2G2 = array();
    
  //   array_push($fecha2G1, ["sergiov", "Seguidor"]);
  //   array_push($fecha2G1, ["alex6", "Participante"]);
  //   array_push($fecha2G2, ["Participante", "sergiov"]);
  //   array_push($fecha2G2, ["Seguidor", "alex6"]);
  //   //print_r($fecha1); 
  //   array_push($grupo2, $fecha2G1);
  //   array_push($grupo2, $fecha2G2);

  //   array_push($fechas, $grupo1);
  //   array_push($fechas, $grupo2);

  //   $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
  //   // asignamos una competencia con tipo FaseGrupos
  //   $competencia = $repositoryComp->find(18);

  //   $this->saveGrupos($fechas, $competencia);

  //   $response = new Response();
  //   $response->setStatusCode(Response::HTTP_OK);
  //   $response->headers->set('Content-Type', 'application/json');

  //   return $response;
  // }


  // ########################################################################
  // ########### deberia llamarse a la hora de generar encuentros ############

  public function saveFixture($matches, $competencia, $tipoorg){
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
    $frec_jornada = 6;
    //$frec_jornada = $competencia->getFrecuencia();
    // recuperamos el id y la fase de a competencia
    for ($i=1; $i <= count($fixtureEncuentros); $i++) {
      $jornada = $competencia->getFase();
      // le vamos agregando la frecuencia de juego de la competencia a la fecha de inicio
      $dias_frec = $frec_jornada*($i-1);
      $fecha_jornada = date('Y-m-d', strtotime($competencia->getFechaIni()->format('Y-m-d'). ' + '.$dias_frec.' days'));
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
    $repositoryUser = $this->getDoctrine()->getRepository(Usuario::class);

    $em = $this->getDoctrine()->getManager();
    // recorremos todos los encuentros
    for ($i=0; $i < count($encuentros); $i++) {
      $nameComp1 = $encuentros[$i][0];
      $nameComp2 = $encuentros[$i][1];
      // vamos a recuperar los competidores del encuentro
      $competitor1 = $repositoryUser->findOneBy(['nombreUsuario' => $nameComp1]);
      $competitor2 = $repositoryUser->findOneBy(['nombreUsuario' => $nameComp2]);
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

      $this->forward('App\Controller\JornadaController::save', [
        'newJornada'  => $jornadaEncuentro
      ]);
    }

    return $jornadaEncuentro;
  }
}