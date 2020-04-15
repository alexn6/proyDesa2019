<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Utils\Constant;
use App\Entity\Edicion;

use \Datetime;

 /**
 * Edicion controller
 * @Route("/api",name="api_")
 */
class EdicionController extends AbstractFOSRestController
{
    /**
     * Lista de todos las ediciones de un encuentro ordenadas por fecha.
     * @Rest\Get("/confrontation/edition"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function allEditions(Request $request){
        
        $respJson = (object) null;
        $repository = $this->getDoctrine()->getRepository(Edicion::class);

        $idEncuentro = $request->get('idEncuentro');

        // vemos si recibimos algun parametro
        if(!empty($idEncuentro)){
            $editions = $repository->getEditionsByConfrontation($idEncuentro);
            
            // pasamos a un array para procesarlo
            // $editions = json_decode($editions, true);
            //cambiamos el objeto deporte por su nombre
            foreach ($editions as &$valor) {
                $fechaString = $valor['fecha'];
                $valor['fecha'] = $fechaString->format(Constant::FORMAT_DATE);
                // $valor['fecha'] = $fechaString->format('Y-m-d H:i:s');
                // $fechaActual = DateTime::createFromFormat('Y-m-d H:i:s', $fechaString->format('Y-m-d H:i:s'));
                // var_dump($fechaActual);
            }
            $respJson = $editions;
            $statusCode = Response::HTTP_OK;
        }
        else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros.";
        }
        
        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
    
        return $response;
    }

}