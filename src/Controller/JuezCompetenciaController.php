<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;    // para incorporar servicios rest

use App\Entity\Juez;
use App\Entity\JuezCompetencia;
use App\Entity\Competencia;

 /**
 * Predio controller
 * @Route("/api",name="api_")
 */
class JuezCompetenciaController extends AbstractFOSRestController
{
    /**
     * Devuelve todos los jueces de una competencia
     * @Rest\Get("/referees/competition"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getRefereesByCompetition(Request $request){      
        $respJson = (object) null;
        $statusCode;
        $grounds = null;

        $idCompetencia = $request->get('id');

        // vemos si recibimos algun parametro
        if(!empty($idCompetencia)){
            $repository = $this->getDoctrine()->getRepository(JuezCompetencia::class);
            $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);

            $competencia = $repositoryComp->find($idCompetencia);

            if($competencia == null){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "No existe la competencia.";
            }
            else{
                // buscamos los predios de la competencia
                $grounds = $repository->refereesByCompetetition($idCompetencia);
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
     * @Rest\Post("/referee/set-competition"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function asignJudgeCompetition(Request $request){

        $respJson = (object) null;
        $statusCode;

        // vemos si existe un body
        if(!empty($request->getContent())){
  
          $repository = $this->getDoctrine()->getRepository(Juez::class);
          $repositoryJuezComp = $this->getDoctrine()->getRepository(JuezCompetencia::class);
          $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);

          // recuperamos los datos del body y pasamos a un array
          $dataRequest = json_decode($request->getContent());
          
          if(!$this->correctDataCreate($dataRequest)){
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros o cuentan con nombres erroneos.";
          }
          else{
              // recuperamos los datos del body
              $idJuez = $dataRequest->idJuez;
              $idCompetencia = $dataRequest->idCompetencia;
                
              // controlamos que la competencia y el juez exista
              $competencia = $repositoryComp->find($idCompetencia);
              $juez = $repository->find($idJuez);

              if(empty($competencia) || empty($juez)){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Competencia o juez inexistente";
              }
              else{
                if($repositoryJuezComp->findOneBy(['juez' => $juez, 'competencia'=> $competencia])){
                    $statusCode = Response::HTTP_BAD_REQUEST;
                    $respJson->messaging = "Juez ya asignado a la competencia";
                }
                else{
                        // asignamos el juez a la competencia
                        $newJuezComp = new JuezCompetencia();
                        $newJuezComp->setJuez($juez);
                        $newJuezComp->setCompetencia($competencia);
                        
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($newJuezComp);
                        $em->flush();
                
                        $statusCode = Response::HTTP_CREATED;
                        $respJson->messaging = "Asignacion exitosa";
                }
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
     * Elimina un juez de la competencia
     * @Rest\Delete("/referee/del-competition"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function deleteJudgeCompetition(Request $request){

        $respJson = (object) null;
        $statusCode;

        $idCompetencia = $request->get('idCompetencia');
        $idJuez = $request->get('idJuez');
      
        // vemos si recibimos el id de un predio para eliminarlo
        if(empty($idCompetencia)){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Peticion mal formada. Falta idCompetencia";
        }else{
            if(empty($idJuez)){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Peticion mal formada. Falta idJuez.";
            }
            else{          
                $repository = $this->getDoctrine()->getRepository(Juez::class);
                $repositoryJuezComp = $this->getDoctrine()->getRepository(JuezCompetencia::class);
                $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
                
                $competencia = $repositoryComp->find($idCompetencia);
                $juez = $repository->find($idJuez);
                if(empty($competencia) || empty($juez)){
                  $statusCode = Response::HTTP_BAD_REQUEST;
                  $respJson->messaging = "Competencia o juez inexistente.";
                }
                else{
                    $juezcomp = $repositoryJuezComp->findOneBy(['competencia'=>$competencia,'juez'=>$juez]);
                    if($juezcomp == null){
                        $statusCode = Response::HTTP_OK;
                        $respJson->messaging = "El juez y la competencia recibidos no estan vinculados.";
                    }
                    else{
                        // eliminamos el dato y refrescamos la DB
                        $em = $this->getDoctrine()->getManager();
                        $em->remove($juezcomp);
                        $em->flush();
            
                        $statusCode = Response::HTTP_OK;
                        $respJson->messaging = "El juez fue desligado de la competencia.";
                    }
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
        if(!property_exists((object) $dataRequest,'idJuez')){
            return false;
        }
        if(!property_exists((object) $dataRequest,'idCompetencia')){
            return false;
        }
        return true;
    }

    
}