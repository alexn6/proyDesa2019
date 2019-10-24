<?php

namespace App\Controller;

//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\JsonResponse;
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

use App\Utils\Constant;

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
          $cant_grupos = $dataCompetitionRequest->cant_grupos;
          if(!empty($cant_grupos)){
            $competenciaCreate->setCantGrupos($cant_grupos);
          }
  
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
     
     //  $respJson = json_encode($respJson);
 
 
       $response = new Response($array_comp);
       $response->setStatusCode($statusCode);
       $response->headers->set('Content-Type', 'application/json');
 
       return $response;
     }
 
}