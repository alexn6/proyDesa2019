<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Turno;

/**
 * Categorias controller
 * @Route("/api",name="api_")
 */
class TurnoController extends AbstractFOSRestController
{
    /**
     * Crea un predio.
     * @Rest\Post("/turn"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function create(Request $request){

        $respJson = (object) null;
        $statusCode;

        $idCompetencia = $request->get('idCompetencia');
      
        // vemos si recibimos el id de un predio para eliminarlo
        if(!empty($idCompetencia)){
            // vemos si existe un body
        if(!empty($request->getContent())){
            // recuperamos los datos del body y pasamos a un array
            $dataTurnoRequest = json_decode($request->getContent());
            
            if(!$this->correctDataCreate($dataPredioRequest)){
              $respJson->success = false;
              $statusCode = Response::HTTP_BAD_REQUEST;
              $respJson->messaging = "Peticion mal formada. Faltan parametros o cuentan con nombres erroneos.";
            }
            else{
                // recuperamos los datos del body
                $hora = $dataPredioRequest->hora;
                
                $this->correctTurno($idCompetencia, $hora);
                
                // tmb deberiamos controlar que no haya un turno con una distancia de tiempo
                // cercana a la frecuencia de la competencia
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
      return true;
    }

    // controlamos que no exista un turno igual al q se quiere crear o cercano en hs y la
    // frecuencia de la competencia
    private function correctTurno($idCompetencia, $hora){
      $repositoryTurno = $this->getDoctrine()->getRepository(Turno::class);
      // controlamos que no haya un turno igual
      $turno = $repositoryTurno->findOneBy(['competencia' => $idCompetencia, 'hora' => $hora]);

      if($turno != null){
        return false;
      }

      
      
    }
}