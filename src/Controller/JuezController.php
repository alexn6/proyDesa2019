<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Juez;

 /**
 * Predio controller
 * @Route("/api",name="api_")
 */
class JuezController extends AbstractFOSRestController
{
    /**
     * Lista de todos las jueces.
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
            'ignored_attributes' => ['juezcompetencia']
        ]);

        $response = new Response($judges);
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
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros o cuentan con nombres erroneos.";
          }
          else{
            // recuperamos los datos del body
            $nombre = $dataJuezRequest->nombre;
            $apellido = $dataJuezRequest->apellido;
            $dni = $dataJuezRequest->dni;
              
            // controlamos que no exista un juez con el mismo dni
            $juezDb = $repository->findOneBy(['dni' => $dni]);

            if($juezDb != NULL){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Ya existe un registro con el dni ingresado.";
            }
            else{
                // creamos el juez
                $newJuez = new Juez();
                $newJuez->setNombre($nombre);
                $newJuez->setApellido($apellido);
                $newJuez->setDni($dni);
        
                $em = $this->getDoctrine()->getManager();
                $em->persist($newJuez);
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

    // ######################################################################################
    // ############################ funciones auxiliares ####################################

    // controlamos que los datos recibidos esten completos
    private function correctDataCreate($dataRequest){
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