<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use \Datetime;

use App\Entity\Competencia;
use App\Entity\Categoria;
use App\Entity\TipoOrganizacion;
use App\Entity\Usuario;
use App\Entity\UsuarioCompetencia;
use App\Entity\Rol;
use App\Entity\Jornada;
use App\Entity\Encuentro;
use App\Entity\Resultado;
use App\Entity\Deporte;

use App\Utils\Constant;
use App\Utils\TablePositionService;

/**
 * Competencia controller
 * @Route("/api",name="api_")
 */
class CompetenciaController extends AbstractFOSRestController
{

  /**
     * Lista de todos las competencias.
     * @Rest\Get("/competitions"), defaults={"_format"="json"})
     * 
     * @return Response
     */
  public function allCompetition()
  {

    $repository=$this->getDoctrine()->getRepository(Competencia::class);
    $competitions=$repository->findall();

    $competitions = $this->get('serializer')->serialize($competitions, 'json', [
      'circular_reference_handler' => function ($object) {
        return $object->getId();
      },
      'ignored_attributes' => ['usuarioscompetencias', '__initializer__', '__cloner__', '__isInitialized__']
    ]);

    // Convert JSON string to Array
    $array_comp = json_decode($competitions, true);

    foreach ($array_comp as &$valor) {
      $valor['categoria']['deporte'] = $valor['categoria']['deporte']['nombre'];
    }

    $array_comp = json_encode($array_comp);

    $response = new Response($array_comp);
    $response->setStatusCode(Response::HTTP_OK);
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }

  /**
     * Crea una competencia.
     * @Rest\Post("/competition"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function create(Request $request){

      $respJson = (object) null;
      $statusCode;
      // vemos si existe un body
      if(!empty($request->getContent())){

        $repository=$this->getDoctrine()->getRepository(Competencia::class);
        $repository_cat=$this->getDoctrine()->getRepository(Categoria::class);
        $repository_tipoorg=$this->getDoctrine()->getRepository(TipoOrganizacion::class);
        $repository_user=$this->getDoctrine()->getRepository(Usuario::class);

        // recuperamos los datos del body y pasamos a un array
        $dataCompetitionRequest = json_decode($request->getContent());      
        // var_dump($dataCompetitionRequest);

        $nombre_comp = $dataCompetitionRequest->nombre;
          
        // controlamos que el nombre de usuario este disponible
        $competencia = $repository->findOneBy(['nombre' => $nombre_comp]);
        if($competencia){
          $respJson->success = false;
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "El nombre de la competencia esta en uso";
        }
        else{
          $format = 'Y-m-d';

          $fecha_ini = DateTime::createFromFormat($format, $dataCompetitionRequest->fecha_ini);
          $fecha_fin = DateTime::createFromFormat($format, $dataCompetitionRequest->fecha_fin);
          // buscamos los datos de los id recibidos
          $categoria = $repository_cat->find($dataCompetitionRequest->categoria_id);
          $tipoorg = $repository_tipoorg->find($dataCompetitionRequest->tipoorg_id);
          $user_creator = $repository_user->find($dataCompetitionRequest->user_id);
          // creamos la competencia
          $competenciaCreate = new Competencia();

          try
          {
            $competenciaCreate->setGenero($dataCompetitionRequest->genero);
          }
          catch (\Exception $e)
          {
            $statusCode = Response::HTTP_INSUFFICIENT_STORAGE;
            $respJson->success = false;
            $respJson->messaging = "ERROR-> " . $e->getMessage();

            $respJson = json_encode($respJson);

            $response = new Response($respJson);
            $response->headers->set('Content-Type', 'application/json');
            $response->setStatusCode($statusCode);

            return $response;
        }

          $competenciaCreate->setNombre($nombre_comp);
          $competenciaCreate->setFechaIni($fecha_ini);
          $competenciaCreate->setFechaFin($fecha_fin);
          $competenciaCreate->setCiudad($dataCompetitionRequest->ciudad);
          $competenciaCreate->setMaxCompetidores($dataCompetitionRequest->max_comp);
          $competenciaCreate->setCategoria($categoria);
          $competenciaCreate->setOrganizacion($tipoorg);

          // vemos si recibimos una cant de grupos 
          // $cant_grupos = $dataCompetitionRequest->cant_grupos;
          // if(!empty($cant_grupos)){
          //   $competenciaCreate->setCantGrupos($cant_grupos);
          // }
          $hayGrupos = property_exists((object) $dataCompetitionRequest,'cant_grupos');
          if($hayGrupos){
            $cant_grupos = $dataCompetitionRequest->cant_grupos;
            $competenciaCreate->setCantGrupos($cant_grupos);
          }

          // recuperamos la fase de la competencia(solo en caso de q sea eliminitorias deberia ser != null)
          $fase = null;
          $existeFase = property_exists((object) $dataCompetitionRequest,'fase');
          if($existeFase){
            $fase = $dataCompetitionRequest->fase;
          }

          // seteamos la fase de la competencia
          $this->setFaseCompetition($competenciaCreate, $fase);
  
          // persistimos la nueva competencia
          $em = $this->getDoctrine()->getManager();
          $em->persist($competenciaCreate);
          $em->flush();

          // creamos el registro del usuario como organizador
          $repositoryRol=$this->getDoctrine()->getRepository(Rol::class);
          $rolOrganizador = $repositoryRol->findOneBy(['nombre' => Constant::ROL_ORGANIZADOR]);
          $newUserOrganizator = new UsuarioCompetencia();
          $newUserOrganizator->setUsuario($user_creator);
          $newUserOrganizator->setCompetencia($competenciaCreate);
          $newUserOrganizator->setRol($rolOrganizador);
          $newUserOrganizator->setAlias("org");
          
          // persistimos el registro
          $em = $this->getDoctrine()->getManager();
          $em->persist($newUserOrganizator);
          $em->flush();
          
          $statusCode = Response::HTTP_CREATED;

          $respJson->success = true;
          $respJson->messaging = "Creacion exitosa";
        }
      }
      else{
        $respJson->success = false;
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Peticion mal formada";
      }

      
      $respJson = json_encode($respJson);

      $response = new Response($respJson);
      $response->headers->set('Content-Type', 'application/json');
      $response->setStatusCode($statusCode);

      return $response;
  }

    /**
     * 
     * @Rest\Post("/existcompetition")
     * 
     * @return Response
     */
    public function existCompetition(Request $request){

        $existCompetition = true;
        $repository=$this->getDoctrine()->getRepository(Competencia::class);
  
        $nombreCompetencia = $request->get('competencia');
        
        $competition = $repository->findOneBy(['nombre' => $nombreCompetencia]);
  
        if (!$competition) {
            $existCompetition = false;
        }
  
        $respJson = (object) null;
        $respJson->existe = $existCompetition;
  
        $respJson = json_encode($respJson);
  
        $response = new Response($respJson);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
  
        return $response;
    }

    /**
     * 
     * @Rest\Post("/competition/org")
     * 
     * @return Response
     */
    public function faseGrupoCompetition(Request $request){
      $respJson = (object) null;

      $idCompetencia = $request->get('idCompetencia');

      if(!empty($idCompetencia)){
        $repository = $this->getDoctrine()->getRepository(Competencia::class);
        $competition = $repository->find($idCompetencia);

        if(!$competition) {
          $respJson->messaging = "La competencia no existe.";
          $statusCode = Response::HTTP_OK;
        }
        else{
          $repositoryJornada = $this->getDoctrine()->getRepository(Jornada::class);
          
          $respJson->cant_grupo = $competition->getCantGrupos();
          $stringCantJornada = $repositoryJornada->nJornadaCompetetion($idCompetencia)[0][1];
          $respJson->cant_jornada = (int)$stringCantJornada;
          $respJson->messaging = "Operacion realizada con exito";
        }
      }
      else{
        $respJson->messaging = "Solicitud mal formada. Faltan parametros.";
        $statusCode = Response::HTTP_BAD_REQUEST;
      }
      

      $respJson = json_encode($respJson);

      $response = new Response($respJson);
      $response->setStatusCode(Response::HTTP_OK);
      $response->headers->set('Content-Type', 'application/json');

      return $response;
  }

    // filtros para buscar competencias
    /**
     * 
     * @Rest\Get("/competitions/filter")
     * 
     * @return Response
     */
    public function filterCompetitions(Request $request)
    {
      // ver este ejemplo => 'SELECT u FROM ForumUser u WHERE (u.username = :name OR u.username = :name2) AND u.id = :id'
      $repository=$this->getDoctrine()->getRepository(Competencia::class);

      $respJson = (object) null;

      // recuperamos los parametros recibidos
      $idCategoria = $request->get('categoria');
      $idTipoOrg = $request->get('tipo_organizacion');
      $genero = $request->get('genero');
      $idDeporte = $request->get('deporte');
      $nombreCompetencia = $request->get('competencia');
      $ciudad = $request->get('ciudad');
      
      // en el caso de no recibir datos le asginamos un null para mantener
      // la cantidad de parametros de la consulta
      if(empty($idCategoria)){
        $idCategoria = null;
      }
      if(empty($idTipoOrg)){
        $idTipoOrg = null;
      }
      if(empty($idDeporte)){
        $idDeporte = null;
      }
      if(empty($genero)){
        $genero = null;
      }
      if(empty($nombreCompetencia)){
        $nombreCompetencia = null;
      }
      if(empty($ciudad)){
        $ciudad = null;
      }
      
      $respJson = $repository->filterCompetitions($nombreCompetencia, $idCategoria, $idDeporte, $idTipoOrg, $genero, $ciudad);
      // pasamos a json el resultado
      $respJson = json_encode($respJson);

      $response = new Response($respJson);
      $response->setStatusCode(Response::HTTP_OK);
      $response->headers->set('Content-Type', 'application/json');

      return $response;
  }

  /**
     * 
     * @Rest\Get("/competition/classified")
     * Pre: solo comptempla competencias del tipo Eliminatorias y FaseGrupos
     * Buscamos los clasificados de una competencia
     * 
     * @return Response
     */
    public function getClassifiedCompetition(Request $request){
      $idCompetition = $request->get('idCompetencia');
      $fase = $request->get('fase');

      $respJson = (object) null;
      $statusCode;
     
      // vemos si recibimos algun parametro
      if(!empty($idCompetition)){
          $repository = $this->getDoctrine()->getRepository(Competencia::class);
          $competition = $repository->find($idCompetition);

          $existWinners = false;

          if(empty($competition)){
              $statusCode = Response::HTTP_BAD_REQUEST;
              $respJson->msg = "La competencia no existe o fue eliminada";
          }
          else{
            $msg;
            $winners = NULL;
            // buscamos todos los encuentros de la fase actual de la competencia
            $repositoryEnc = $this->getDoctrine()->getRepository(Encuentro::class);
            $encuentrosFase = $repositoryEnc->findEncuentrosByCompetenciaFase($idCompetition, $competition->getFaseActual());
            if($this->faseCompleted($encuentrosFase)){
              // controlamos el tipo de organizacion
              $typeOrganization = $competition->getOrganizacion()->getCodigo();
              // ################################## ELIMINATORIAS ########################################
              if($typeOrganization == Constant::COD_TIPO_ELIMINATORIAS){
                $winners = $this->getWinnersEliminatorias($encuentrosFase);
                // serializamos y decodificamos los resultados
                $winners = $this->get('serializer')->serialize($winners, 'json', [
                    'circular_reference_handler' => function ($object) {
                      return $object->getId();
                    },
                    'ignored_attributes' => ['usuario', 'competencia', '__initializer__', '__cloner__', '__isInitialized__']
                  ]);
                $respJson = $winners;
                $existWinners = true;
              }
              // ############################# ELIMINATORIAS DOBLES #####################################
              if($typeOrganization == Constant::COD_TIPO_ELIMINATORIAS_DOUBLE){
              // (en el caso de ser doble eliminatoria ver como determinar el ganador)
              }
              // ############################# FASE DE GRUPOS #####################################
              if($typeOrganization == Constant::COD_TIPO_FASE_GRUPOS){
                if(!empty($fase)){
                  $tableGral = null;
                  $cantClassified = pow(2, $fase);
                  // recuperamos la tabla de cada grupo
                  $tablesAllGroup = array();
                  $repository = $this->getDoctrine()->getRepository(Resultado::class);
                  for ($i=0; $i < $competition->getCantGrupos() ; $i++) {
                    $resultados = $repository->findResultCompetitorsGroup($competition->getId(), $i+1);
                    // pasamos los resultado a un array para poder trabajarlo
                    $resultados = $this->get('serializer')->serialize($resultados, 'json', [
                        'circular_reference_handler' => function ($object) {
                            return $object->getId();
                        },
                        'ignored_attributes' => ['competencia', 'competidor']
                    ]);
                    // pasamos los reultados a un array para poder trabajarlos
                    $resultados = json_decode($resultados, true);
  
                    $servPosition = new TablePositionService();
                    $pointsBySport = $this->getPointsBySport($competition);
                    $tableGroup = $servPosition->getTablePosition($resultados, $pointsBySport);
                    array_push($tablesAllGroup, $tableGroup);
                  }
                  // hacemos una tabla general a partir de todas las tablas
                  $tableGral = $this->getTableComplete($tablesAllGroup);
                  //var_dump($tableGral);
                  $tableMin = array_slice($tableGral, 0, $cantClassified);
                  $winners = $this->getWinnersGrupos($tableMin);
                  
                  $winners = $this->get('serializer')->serialize($winners, 'json', [
                    'circular_reference_handler' => function ($object) {
                      return $object->getId();
                    },
                    'ignored_attributes' => ['usuario', 'competencia', '__initializer__', '__cloner__', '__isInitialized__']
                  ]);

                  $respJson = $winners;
                  $existWinners = true;
                }
                else{
                  $respJson->msg = "Debe seleccionar la fase siguiente";
                }
              }
            }
            else{
              $respJson->msg = "Deben resolverse todos los encuentros de la fase";
            }
            $statusCode = Response::HTTP_OK;
          }
      }
      else{
        $respJson->msg = "Solicitud mal formada";
        $statusCode = Response::HTTP_BAD_REQUEST;
      }

      if(!$existWinners){
        $respJson = json_encode($respJson);
      }
      $response = new Response($respJson);
      
      //$response = new Response($winners);
      $response->setStatusCode($statusCode);
      $response->headers->set('Content-Type', 'application/json');

      return $response;
    }
  
    // ########################################################
    // ################### funciones auxiliares ################
 
    	 /**
     * 
     * @Rest\GET("/findCompetitionsByName/{nameCompetition}"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function findCompetitionsByName($nameCompetition){

      // var_dump($request);
       $repository = $this->getDoctrine()->getRepository(Competencia::class);
 
      // $name = $nameCompetiton;
 
       if(!empty($nameCompetition)){
          $competitions = $repository->findCompetitionsByName($nameCompetition);
    
          $competitions = $this->get('serializer')->serialize($competitions, 'json', [
            'circular_reference_handler' => function ($object) {
              return $object->getId();
            },
            'ignored_attributes' => ['usuarioscompetencias', '__initializer__', '__cloner__', '__isInitialized__']
          ]);
      
          $array_comp = json_decode($competitions, true);
    
          foreach ($array_comp as &$valor) {
            $valor['categoria']['deporte'] = $valor['categoria']['deporte']['nombre'];
          }
      
          $array_comp = json_encode($array_comp);
      
          // $response = new Response($competitions);
          $response = new Response($array_comp);
        
          $statusCode = Response::HTTP_OK;
        
      }else{
          $respJson->competitions = NULL;
          $statusCode = Response::HTTP_BAD_REQUEST;
      }
 
       $response = new Response($array_comp);
       $response->setStatusCode($statusCode);
       $response->headers->set('Content-Type', 'application/json');
 
       return $response;
     }

     // para probar

     /**
     * Lista de todos las competencias.
     * @Rest\Get("/competitions-roles"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function competitionRolesByUser(Request $request)
    {
      $respJson = (object) null;

      // recuperamos los parametros recibidos
      $idUsuario = $request->get('idUsuario');
      $idCategoria = $request->get('categoria');
      $idTipoOrg = $request->get('tipo_organizacion');
      $genero = $request->get('genero');
      $idDeporte = $request->get('deporte');
      $nombreCompetencia = $request->get('competencia');
      $ciudad = $request->get('ciudad');
      
      // en el caso de no recibir datos le asginamos un null para mantener
      // la cantidad de parametros de la consulta
      if(empty($idCategoria)){
        $idCategoria = null;
      }
      if(empty($idTipoOrg)){
        $idTipoOrg = null;
      }
      if(empty($idDeporte)){
        $idDeporte = null;
      }
      if(empty($genero)){
        $genero = null;
      }
      if(empty($nombreCompetencia)){
        $nombreCompetencia = null;
      }
      if(empty($ciudad)){
        $ciudad = null;
      }

      // var_dump($request);
      $repository = $this->getDoctrine()->getRepository(Competencia::class);
 
      // $name = $nameCompetiton;
 
      if(!empty($idUsuario)){
        $competitions = $repository->filterCompetitionsByUserFull($idUsuario, $nombreCompetencia, $idCategoria, $idDeporte, $idTipoOrg, $genero, $ciudad);

        $competitions = $this->get('serializer')->serialize($competitions, 'json', [
          'circular_reference_handler' => function ($object) {
            return $object->getId();
          }
        ]);
        $statusCode = Response::HTTP_OK;
      }
      else{
         $respJson->competitions = NULL;
         $statusCode = Response::HTTP_BAD_REQUEST;
      }
 
      $response = new Response($competitions);
      $response->setStatusCode($statusCode);
      $response->headers->set('Content-Type', 'application/json');

      return $response;
    }

     /**
     * Lista de todos las competencias.
     * @Rest\Get("/competitions-rol"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function competitionRolByUser(Request $request)
    {
      $respJson = (object) null;

      // recuperamos los parametros recibidos
      $idUsuario = $request->get('idUsuario');
      $idCategoria = $request->get('categoria');
      $idTipoOrg = $request->get('tipo_organizacion');
      $genero = $request->get('genero');
      $idDeporte = $request->get('deporte');
      $nombreCompetencia = $request->get('competencia');
      $ciudad = $request->get('ciudad');

      // var_dump($request);
      $repository = $this->getDoctrine()->getRepository(Competencia::class);
 
      if(!empty($idUsuario)){
        $competitions = $repository->filterCompetitionsRol($idUsuario, $nombreCompetencia, $idCategoria, $idDeporte, $idTipoOrg, $genero, $ciudad);

        $competitions = $this->get('serializer')->serialize($competitions, 'json', [
          'circular_reference_handler' => function ($object) {
            return $object->getId();
          }
        ]);
    
        $array_comp = json_decode($competitions, true);
        $array_comp = json_encode($array_comp);
        
        $statusCode = Response::HTTP_OK;
      }
      else{
         $respJson->competitions = NULL;
         $statusCode = Response::HTTP_BAD_REQUEST;
      }
 
      $response = new Response($array_comp);
      $response->setStatusCode($statusCode);
      $response->headers->set('Content-Type', 'application/json');

      return $response;
    }

     /**
     * Lista de todos las competencias.
     * @Rest\Get("/competitions-unrol"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function competitionUnrolByUser(Request $request)
    {
      $respJson = (object) null;

      // recuperamos los parametros recibidos
      $idUsuario = $request->get('idUsuario');
      $idCategoria = $request->get('categoria');
      $idTipoOrg = $request->get('tipo_organizacion');
      $genero = $request->get('genero');
      $idDeporte = $request->get('deporte');
      $nombreCompetencia = $request->get('competencia');
      $ciudad = $request->get('ciudad');

      // var_dump($request);
      $repository = $this->getDoctrine()->getRepository(Competencia::class);
 
      if(!empty($idUsuario)){
        $competitions = $repository->filterCompetitionsUnrol($idUsuario, $nombreCompetencia, $idCategoria, $idDeporte, $idTipoOrg, $genero, $ciudad);

        $competitions = $this->get('serializer')->serialize($competitions, 'json', [
          'circular_reference_handler' => function ($object) {
            return $object->getId();
          }
        ]);
    
        $array_comp = json_decode($competitions, true);
        $array_comp = json_encode($array_comp);
        
        $statusCode = Response::HTTP_OK;
      }
      else{
         $respJson->competitions = NULL;
         $statusCode = Response::HTTP_BAD_REQUEST;
      }
 
      $response = new Response($array_comp);
      $response->setStatusCode($statusCode);
      $response->headers->set('Content-Type', 'application/json');

      return $response;
    }

    // #####################################################################################
    // ################################ funciones privadas ################################

    // controlamos que la cant de competidores se adecue a la competencia
    private function setFaseCompetition($competencia, $fase){
      // aca sacamos la funcion de control de cada tipo de organizacion
      $codigoTipo = $competencia->getOrganizacion()->getCodigo();
      
      if(($codigoTipo == 'ELIM')||($codigoTipo == 'ELIMDOUB')){
          $competencia->setFase($fase);
          $competencia->setFaseActual($fase);
      }
      else{
          if(($codigoTipo == 'LIGSING')||($codigoTipo == 'LIGDOUB')){
            $competencia->setFase(1);
            $competencia->setFaseActual(1);
          }
          // sino es un grupo (0 -> fase de grupos)
          else{
            $competencia->setFase(0);
            $competencia->setFaseActual(0);
          }
      }
    }

    // controlamos que los encuentros recibidos cuenten con un resultado
    private function faseCompleted($encuentrosFase){
      for ($i=0; $i < count($encuentrosFase); $i++) {
        $rdoC1 = $encuentrosFase[$i]->getRdoComp1();
        $rdoC2 = $encuentrosFase[$i]->getRdoComp2();
        if(($rdoC1 === NULL) || ($rdoC2 === NULL)){
          return false;
        }
      }

      return true;
    }

    /* Determina los ganadores de los encuentros recibidos
    ** Pre: los encuentros cuentan con resultados sin empates
    */
    private function getWinnersEliminatorias($encuentrosFase){
      $winners = array();
      for ($i=0; $i < count($encuentrosFase); $i++) {
        $rdoC1 = $encuentrosFase[$i]->getRdoComp1();
        $rdoC2 = $encuentrosFase[$i]->getRdoComp2();
        // guardamos el ganador
        if($rdoC1 > $rdoC2){
          array_push($winners, $encuentrosFase[$i]->getCompetidor1());
        }
        else{
          array_push($winners, $encuentrosFase[$i]->getCompetidor2());
        }
      }

      return $winners;
    }

    // Recupera los competidores de la tabla de posiciones recibida
    private function getWinnersGrupos($tablePositions){
      $winners = array();
      $repository = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
      for ($i=0; $i < count($tablePositions); $i++) {
        // buscamos el competidor
        $competidor = $repository->find($tablePositions[$i]['id']);
        array_push($winners, $competidor);
      }

      return $winners;
    }

    // Obtiene una tabla de posiciones gral del conjunto de tablas de los n grupos de una competencia
    // Ordenada por puntos y por posicion en el grupos
    private function getTableComplete($tablesAllGroup){
      $tableGral = array();
      $cantTables = count($tablesAllGroup);
      $cantCompetitorsByTable = count($tablesAllGroup[0]);
      // vamos sacando los mejores de cada tabla
      for ($j=0; $j < $cantCompetitorsByTable; $j++) {
        $posicionesNAllTables = array();
        // recuperamos los n primeros de cada tabla
        for ($i=0; $i < $cantTables; $i++) {
          array_push($posicionesNAllTables, $tablesAllGroup[$i][$j]);
        }
        // ordenamos los resultados por puntos
        usort($posicionesNAllTables, function($a, $b) {
              return strnatcmp($a['Pts'], $b['Pts']);
          }
        );
        // agregamos las posiciones ordenadas a la tabla gral
        $tableGral = array_merge($tableGral, $posicionesNAllTables);
      }

      return  array_reverse($tableGral);
    }

    // buscamos los puntos por resultado del encuentro segun el deporte
    private function getPointsBySport($competencia){
      $ptsByResults = array();
      $repositoryDep = $this->getDoctrine()->getRepository(Deporte::class);
      $pts = $repositoryDep->findPointByResultSport($competencia->getCategoria()->getDeporte()->getId());

      $pts = $this->get('serializer')->serialize($pts, 'json', [
          'circular_reference_handler' => function ($object) {
              return $object->getId();
          },
          'ignored_attributes' => ['competencia']
      ]);

      // pasamos los reultados a un array para poder trabajarlos
      $pts = json_decode($pts, true);
      $ptsByResults["ganado"] = $pts[0]['ppg'];
      $ptsByResults["empatado"] = $pts[0]['ppe'];
      $ptsByResults["perdido"] = $pts[0]['ppp'];
      
      return $ptsByResults;
  }
 
}