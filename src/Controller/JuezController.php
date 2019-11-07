<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Juez;
use App\Entity\Competencia;

 /**
 * Predio controller
 * @Route("/api",name="api_")
 */
class JuezController extends AbstractFOSRestController
{
    /**
     * Lista de todos las predios.
     * @Rest\Get("/referees"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function allReferees()
    {

        $repository = $this->getDoctrine()->getRepository(Juez::class);
        $judges = $repository->findall();

        $judges = $this->get('serializer')->serialize($judges, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            },
            'ignored_attributes' => ['competencia']
        ]);

        $response = new Response($judges);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Devuelve todas los predios de una competencia
     * @Rest\Get("/referees/competition"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getJudgesByCompetition(Request $request){
        $repository = $this->getDoctrine()->getRepository(Juez::class);
      
        $respJson = (object) null;

        $idCompetencia = $request->get('idCompetencia');

        // vemos si recibimos algun parametro
        if(!empty($idCompetencia)){
            $judges = $repository->findJudgesByCompetetition($idCompetencia);
            $statusCode = Response::HTTP_OK;

            $judges = $this->get('serializer')->serialize($judges, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                },
                'ignored_attributes' => ['competencia']
            ]);
            $respJson->messaging = "Operacion con exito";
        }
        else{
            $judges  = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Faltan parametros";
        }
        
        $respJson->judges = json_decode($judges);
        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
    
        return $response;
    }

    /**
     * Crea un juez.
     * @Rest\Post("/judge"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function create(Request $request){

        $respJson = (object) null;
        $statusCode;

        // vemos si existe un body
        if(!empty($request->getContent())){
  
          $repository=$this->getDoctrine()->getRepository(Juez::class);
    
          // recuperamos los datos del body y pasamos a un array
          $dataJuezRequest = json_decode($request->getContent());
          
          if(!$this->correctDataCreate($dataJuezRequest)){
            $respJson->success = false;
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros o cuentan con nombres erroneos.";
          }
          else{
            // recuperamos los datos del body
            $nombre = $dataJuezRequest->nombre;
            $idCompetencia = $dataJuezRequest->idCompetencia;
            $apellido = $dataJuezRequest->apellido;
            $dni = $dataJuezRequest->dni;
              
            // controlamos que la competencia exista
            $repositoryComp=$this->getDoctrine()->getRepository(Competencia::class);
            $competencia = $repositoryComp->find($idCompetencia);

            if($competencia == NULL){
                $respJson->success = false;
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Competencia inexistente";
            }
            else{
                // creamos el juez
                $newJuez = new Juez();
                $newJuez->setNombre($nombre);
                $newJuez->setApellido($apellido);
                $newJuez->setCompetencia($competencia);
                $newJuez->setDni($dni);
        
                $em = $this->getDoctrine()->getManager();
                $em->persist($newJuez);
                $em->flush();
        
                $statusCode = Response::HTTP_CREATED;
        
                $respJson->success = true;
                $respJson->messaging = "Creacion exitosa";
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
     * @Rest\Delete("/judge/del"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function delete(Request $request){

        $respJson = (object) null;
        $statusCode;

        $idJuez = $request->get('idJuez');
      
        // vemos si recibimos el id de un predio para eliminarlo
        if(empty($idJuez)){
            $respJson->success = false;
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros.";
        }
        else{
            $repository=$this->getDoctrine()->getRepository(Juez::class);
            $juez = $repository->find($idJuez);
            if($juez == NULL){
                $respJson->success = true;
                $statusCode = Response::HTTP_OK;
                $respJson->messaging = "El juez no existe o ya fue eliminado";
            }
            else{
                // eliminamos el dato y refrescamos la DB
                $em = $this->getDoctrine()->getManager();
                $em->remove($juez);
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
        if(!property_exists((object) $dataRequest,'apellido')){
            return false;
        }
        if(!property_exists((object) $dataRequest,'dni')){
            return false;
        }

        return true;
    }
}