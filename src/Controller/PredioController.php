<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;    // para incorporar servicios rest

use App\Entity\Predio;
use App\Entity\Competencia;
use App\Entity\Campo;

use App\Utils\Constant;

use App\Controller\CampoController;

 /**
 * Predio controller
 * @Route("/api",name="api_")
 */
class PredioController extends AbstractFOSRestController
{
    /**
     * Lista de todos las predios.
     * @Rest\Get("/grounds"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function allGrounds()
    {

        $repository = $this->getDoctrine()->getRepository(Predio::class);
        $grounds = $repository->findall();

        $grounds = $this->get('serializer')->serialize($grounds, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            },
            'ignored_attributes' => ['competencia']
        ]);

        $response = new Response($grounds);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Devuelve todas los predios de una competencia
     * @Rest\Get("/grounds/competition"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getGroundsByCompetition(Request $request){
        $repository=$this->getDoctrine()->getRepository(Predio::class);
      
        $respJson = (object) null;

        $idCompetencia = $request->get('idCompetencia');

        // vemos si recibimos algun parametro
        if(!empty($idCompetencia)){
            $grounds = $repository->findGroundsByCompetetition($idCompetencia);
            $statusCode = Response::HTTP_OK;

            $grounds = $this->get('serializer')->serialize($grounds, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                },
                'ignored_attributes' => ['competencia']
            ]);
            $respJson->messaging = "Operacion con exito";
        }
        else{
            $grounds  = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Faltan parametros";
        }

        $respJson->grounds = json_decode($grounds);
        $respJson = json_encode($respJson);
        
        $response = new Response($respJson);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
    
        return $response;
    }

    /**
     * Crea un predio.
     * @Rest\Post("/grounds"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function create(Request $request){

        $respJson = (object) null;
        $statusCode;

        // vemos si existe un body
        if(!empty($request->getContent())){
  
          $repository=$this->getDoctrine()->getRepository(Predio::class);
    
          // recuperamos los datos del body y pasamos a un array
          $dataPredioRequest = json_decode($request->getContent());
          
          if(!$this->correctDataCreate($dataPredioRequest)){
            $respJson->success = false;
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros o cuentan con nombres erroneos.";
          }
          else{
              // recuperamos los datos del body
              $nombre = $dataPredioRequest->nombre;
              $idCompetencia = $dataPredioRequest->idCompetencia;
              $direccion = $dataPredioRequest->direccion;
              $ciudad = $dataPredioRequest->ciudad;
                
              // controlamos que el nombre de predio este disponible
              $predio = $repository->findOneBy(['nombre' => $nombre]);
              if($predio){
                $respJson->success = false;
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "El nombre del predio esta en uso";
              }
              else{
                  // controlamos que la competencia exista
                  $repositoryComp=$this->getDoctrine()->getRepository(Competencia::class);
                  $competencia = $repositoryComp->find($idCompetencia);
                  if($competencia == NULL){
                    $respJson->success = false;
                    $statusCode = Response::HTTP_BAD_REQUEST;
                    $respJson->messaging = "Competencia inexistente";
                  }
                  else{
                    // creamos el predio
                    $newPredio = new Predio();
                    $newPredio->setNombre($nombre);
                    $newPredio->setDireccion($direccion);
                    $newPredio->setCompetencia($competencia);
                    $newPredio->setCiudad($ciudad);
            
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($newPredio);
                    $em->flush();
            
                    $statusCode = Response::HTTP_CREATED;
            
                    $respJson->success = true;
                    $respJson->messaging = "Creacion exitosa";
                  }
              }
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
     * Crea un usuario.
     * @Rest\Delete("/grounds/del"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function delete(Request $request){

        $respJson = (object) null;
        $statusCode;

        $idPredio = $request->get('idPredio');
      
        // vemos si recibimos el id de un predio para eliminarlo
        if(empty($idPredio)){
            $respJson->success = false;
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros.";
        }
        else{            
            $repository=$this->getDoctrine()->getRepository(Predio::class);
            $predio = $repository->find($idPredio);
            if($predio == NULL){
                $respJson->success = true;
                $statusCode = Response::HTTP_OK;
                $respJson->messaging = "El predio no existe o ya fue eliminado";
            }
            else{
                // ######################################################################
                // tmb deberiamos borrar todos los campos de este predio
                // ######################################################################
                // $urlDeleteCampo = Constant::SERVICES_REST_CAMPO.'/fields/del?idPredio='.$idPredio;
                // $client = HttpClient::create();
                // $response = $client->request('DELETE', $urlDeleteCampo);
                //$statusCodeDelete = $response->getStatusCode();

                $repositoryCampos = $this->getDoctrine()->getRepository(Campo::class);
                $campos = $repositoryCampos->findFieldsByCampus($idPredio);
                // eliminamos los campos del predio
                $this->forward('App\Controller\CampoController::deleteSetCampus', [
                    'campos' => $campos
                ]);

                // eliminamos el dato y refrescamos la DB
                $em = $this->getDoctrine()->getManager();
                $em->remove($predio);
                $em->flush();
    
                $respJson->success = true;
                $statusCode = Response::HTTP_OK;
                $respJson->messaging = "Eiminacion correcta";
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
            return false;
        }
        if(!property_exists((object) $dataRequest,'nombre')){
            return false;
        }
        if(!property_exists((object) $dataRequest,'direccion')){
            return false;
        }
        if(!property_exists((object) $dataRequest,'ciudad')){
            return false;
        }

        return true;
    }
}