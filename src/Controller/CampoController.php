<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Campo;
use App\Entity\Predio;

 /**
 * Campo controller
 * @Route("/api",name="api_")
 */
class CampoController extends AbstractFOSRestController
{
    
    /**
     * Lista de todos los campos de un predio.
     * @Rest\Get("/grounds/getFieldsByGrounds"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getFieldsByGrounds(Request $request){
  
        $repository = $this->getDoctrine()->getRepository(Campo::class);
      
        $respJson = (object) null;

        $idPredio = $request->get('idPredio');
        $idCampo =  $request->get('idCampo');
  
        // vemos si recibimos algun parametro
        if(empty($idCampo))
            $idCampo = null;

        if(!empty($idPredio)){
  
            $field = $repository->findFieldsByGrounds($idPredio,$idCampo);
            $statusCode = Response::HTTP_OK;

            $field = $this->get('serializer')->serialize($field, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                },
                'ignored_attributes' => ['predio']
            ]);
        }
        else{
            $field  = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        $response = new Response($field);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
    
        return $response;
    }

    // /**
    //  * Lista de todos los campos de un predio.
    //  * @Rest\Get("/grounds/competition"), defaults={"_format"="json"})
    //  * 
    //  * @return Response
    //  */
    // public function getFieldsByCompetition(Request $request){
  
    //     $repository = $this->getDoctrine()->getRepository(Campo::class);
      
    //     $respJson = (object) null;

    //     $idCompetencia = $request->get('idCompetencia');

    //     if(!empty($idCompetencia)){
    //         // buscamos los campos de los predios asignados a la competencic
    //         $field = $repository->findFieldsByGrounds($idPredio,$idCampo);
    //         $statusCode = Response::HTTP_OK;

    //         $field = $this->get('serializer')->serialize($field, 'json', [
    //             'circular_reference_handler' => function ($object) {
    //                 return $object->getId();
    //             },
    //             'ignored_attributes' => ['predio']
    //         ]);
    //     }
    //     else{
    //         $field  = NULL;
    //         $statusCode = Response::HTTP_BAD_REQUEST;
    //     }

    //     $response = new Response($field);
    //     $response->setStatusCode(Response::HTTP_OK);
    //     $response->headers->set('Content-Type', 'application/json');
    
    //     return $response;
    // }

    /**
     * Crea un campo.
     * @Rest\Post("/grounds/field"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function create(Request $request){

        $respJson = (object) null;
        $statusCode;

        // vemos si existe un body
        if(!empty($request->getContent())){
  
          $repository = $this->getDoctrine()->getRepository(Campo::class);
    
          // recuperamos los datos del body y pasamos a un array
          $dataCampoRequest = json_decode($request->getContent());
          
          if(!$this->correctDataCreate($dataCampoRequest)){
            $respJson->success = false;
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros o cuentan con nombres erroneos.";
          }
          else{
              // recuperamos los datos del body
              $nombre = $dataCampoRequest->nombre;
              $idPredio = $dataCampoRequest->idPredio;

              // controlamos que el predio exista
                $repositoryPred=$this->getDoctrine()->getRepository(Predio::class);
                $predio = $repositoryPred->find($idPredio);
                if($predio == NULL){
                    $respJson->success = false;
                    $statusCode = Response::HTTP_BAD_REQUEST;
                    $respJson->messaging = "Predio inexistente";
                  }
                  else{
                    $campoName = $repository->findFieldsByName($idPredio,$nombre);
                    if($campoName != null){
                        $respJson->success = false;
                        $statusCode = Response::HTTP_BAD_REQUEST;
                        $respJson->messaging = "Nombre de Campo existente";                          
                    } else {                    
                        // creamos el campo
                        $newCampo = new Campo();
                        $newCampo->setNombre($nombre);
                        
                        if(!empty($dataCampoRequest->capacidad)){
                        $capacidad = $dataCampoRequest->capacidad;
                        $newCampo->setCapacidad($capacidad);
                        }

                        if(!empty($dataCampoRequest->dimensiones)){
                        $dimensiones = $dataCampoRequest->dimensiones;
                        $newCampo->setDimensiones($dimensiones);
                        }
                        $newCampo->setPredio($predio);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($newCampo);
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
     * Eliminar todos los campos de un predio o elimina un campo de un predio.
     * @Rest\Delete("/grounds/fields/del"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function delete(Request $request){

        $respJson = (object) null;
        $statusCode;

        $idPredio = $request->get('idPredio');
        $idCampo = $request->get('idCampo');

        if(empty($idCampo)){
            $idCampo = null;
            // vemos si recibimos el id de un predio para eliminarlo
            if(empty($idPredio)){
                $respJson->success = false;
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Peticion mal formada. Faltan parametros.";
            }
            else{
                $repository=$this->getDoctrine()->getRepository(Campo::class);
                $campos = $repository->findFieldsByGrounds($idPredio,$idCampo);
                if($campos == NULL){
                    $respJson->success = true;
                    $statusCode = Response::HTTP_OK;
                    $respJson->messaging = "El predio no tiene campos o fueron eliminados";
                }
                else{
                    // eliminamos todos los campos
                    $this->deleteSetCampus($campos);
    
                    $respJson->success = true;
                    $statusCode = Response::HTTP_OK;
                    $respJson->messaging = "Eiminacion correcta";
                }
            }
        }else{
            if(empty($idPredio)){
                $respJson->success = false;
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Peticion mal formada. Faltan parametros.";
            }
            else{
                $repository=$this->getDoctrine()->getRepository(Campo::class);
                $campo = $repository->findFieldsByGrounds($idPredio, $idCampo);
                if($campo == NULL){
                    $respJson->success = true;
                    $statusCode = Response::HTTP_OK;
                    $respJson->messaging = "El campo no existe o ya fue eliminado";
                }
                else{
                    // eliminamos el dato y refrescamos la DB
                    $this->deleteSetCampus($campo);
    
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
        if(!property_exists((object) $dataRequest,'nombre')){
            return false;
        }
    
        return true;
    }

    // eliminamos un cjto de campos
    public function deleteSetCampus($campos){
        // eliminamos el dato y refrescamos la DB
        $em = $this->getDoctrine()->getManager();
        foreach ($campos as $campo) {
            $em->remove($campo);
        }
        $em->flush();
    }
}