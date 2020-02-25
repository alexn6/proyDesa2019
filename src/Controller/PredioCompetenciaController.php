<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;    // para incorporar servicios rest

use App\Entity\Predio;
use App\Entity\PredioCompetencia;
use App\Entity\Competencia;

 /**
 * Predio controller
 * @Route("/api",name="api_")
 */
class PredioCompetenciaController extends AbstractFOSRestController
{
    /**
     * Devuelve todas los predios de una competencia
     * @Rest\Get("/grounds/competition"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getGroundsByCompetition(Request $request){      
        $respJson = (object) null;
        $statusCode;
        $grounds = null;

        $idCompetencia = $request->get('id');

        // vemos si recibimos algun parametro
        if(!empty($idCompetencia)){
            $repository = $this->getDoctrine()->getRepository(PredioCompetencia::class);
            $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);

            $competencia = $repositoryComp->find($idCompetencia);

            if($competencia == null){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Peticion mal formada. Faltan parametros.";
            }
            else{
                // buscamos los predios de la competencia
                $grounds = $repository->groundsByCompetetition($idCompetencia);
                $respJson = $grounds;
                $statusCode = Response::HTTP_OK;
            }
        }
        else{
            $grounds  = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Faltan parametros";
        }

        $respJson = json_encode($respJson);
        
        $response = new Response($respJson);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
    
        return $response;
    }

    /**
     * Crea un predio.
     * @Rest\Post("/set-ground"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function create(Request $request){

        $respJson = (object) null;
        $statusCode;

        // vemos si existe un body
        if(!empty($request->getContent())){
  
          $repository = $this->getDoctrine()->getRepository(Predio::class);
          $repositoryPredComp = $this->getDoctrine()->getRepository(PredioCompetencia::class);
          $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);

    
          // recuperamos los datos del body y pasamos a un array
          $dataPredioRequest = json_decode($request->getContent());
          
          if(!$this->correctDataCreate($dataPredioRequest)){
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros o cuentan con nombres erroneos.";
          }
          else{
              // recuperamos los datos del body
              $idPredio = $dataPredioRequest->idPredio;
            //   $idCompetencia = $dataPredioRequest->idCompetencia;
              $idCompetencia = $dataPredioRequest->idCompetencia;
                
              // controlamos que la competencia exista
              $competencia = $repositoryComp->find($idCompetencia);
              $predio = $repository->find($idPredio);
              if(empty($competencia) || empty($predio)){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Competencia o predio no existe";
              }
              if($repositoryPredComp->findOneBy(['predio' => $predio, 'competencia'=> $competencia])){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Predio ya asignado a competencia";
                }else{
                
                    // creamos el predio
                    $newPredio = new PredioCompetencia();
                    $newPredio->setPredio($predio);
                    $newPredio->setCompetencia($competencia);
                    
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($newPredio);
                    $em->flush();
            
                    $statusCode = Response::HTTP_CREATED;
                    $respJson->messaging = "Creacion exitosa";
                }
            }
        }
        else{
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
     * Elimina un predio
     * @Rest\Delete("/del-groundCompetition"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function delete(Request $request){

        $respJson = (object) null;
        $statusCode;

        $idCompetencia = $request->get('idCompetencia');
        $idPredio = $request->get('idPredio');
      
        // vemos si recibimos el id de un predio para eliminarlo
        if(empty($idCompetencia)){
                $respJson->success = false;
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Peticion mal formada.";
        }else{
            if(empty($idPredio)){
                $respJson->success = false;
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Peticion mal formada. Faltan parametros.";
            }
            else{          
                $repository = $this->getDoctrine()->getRepository(Predio::class);
                $repositoryPredComp = $this->getDoctrine()->getRepository(PredioCompetencia::class);
                $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
                
                $competencia = $repositoryComp->find($idCompetencia);
                $predio = $repository->find($idPredio);
                if(empty($competencia) || empty($predio)){
                  $statusCode = Response::HTTP_BAD_REQUEST;
                  $respJson->messaging = "Competencia o predio no existe";
                }
                $predio = $repositoryPredComp->findOneBy(['competencia'=>$idCompetencia,'predio'=>$idPredio]);
                if($predio == NULL){
                    $respJson->success = true;
                    $statusCode = Response::HTTP_OK;
                    $respJson->messaging = "El predio y/o competencia incorrecta o inexistente";
                }
                else{
                    // eliminamos el dato y refrescamos la DB
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($predio);
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
        if(!property_exists((object) $dataRequest,'idPredio')){
            return false;
        }
        if(!property_exists((object) $dataRequest,'idCompetencia')){
            return false;
        }
        return true;
    }

    
}