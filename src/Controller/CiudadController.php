<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;    // para incorporar servicios rest

use App\Entity\Ciudad;
use App\Entity\Pais;

 /**
 * Ciudad controller
 * @Route("/api",name="api_")
 */
class CiudadController extends AbstractFOSRestController{
    
     /**
     * Lista de todos las ciudades.
     * @Rest\Get("/cities"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function allCities()
    {

        $repository=$this->getDoctrine()->getRepository(Ciudad::class);
        $cities=$repository->findall();

        $cities = $this->get('serializer')->serialize($cities, 'json', [
        'circular_reference_handler' => function ($object) {
            return $object->getId();
        }
        ]);

        // pasamos a un array para procesarlo
        $array_comp = json_decode($cities, true);
        // cambiamos el objeto deporte por su nombre
        foreach ($array_comp as &$valor) {
           $valor['pais'] = $valor['pais']['nombre'];
        }
        // pasamos todo a json de nuevo
        $array_comp = json_encode($array_comp);

        $response = new Response($array_comp);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * 
     * @Rest\Get("/findCitiesByName"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function findCitiesByName(Request $request){
        $respJson =  null;
        $statusCode;
        
        $nombreCiudad = $request->get('nombreCiudad');
        
        $repository = $this->getDoctrine()->getRepository(Ciudad::class);
   
        if(!empty($nombreCiudad)){
            $ciudades = $repository->findCitiesByName($nombreCiudad);
            
            $ciudades = $this->get('serializer')->serialize($ciudades, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
            ]);
            // pasamos a un array para procesarlo
            $array_comp = json_decode($ciudades, true);
            // cambiamos el objeto deporte por su nombre
            foreach ($array_comp as &$valor) {
                $valor['pais'] = $valor['pais']['nombre'];
            }
            if(empty($array_comp)){
                $respJson = (object) null;
                $respJson->messaging = "No se encontraron ciudades";
                $respJson = json_encode($respJson);
                $statusCode = Response::HTTP_BAD_REQUEST;
            }else{
                $respJson = json_encode($array_comp);        
                $response = new Response($respJson);
                $statusCode = Response::HTTP_OK;
            }
        }else{
            $respJson = (object) null;
            $respJson->messaging = "Faltan parametros";
            $respJson = json_encode($respJson);
            $statusCode = Response::HTTP_BAD_REQUEST;
        }
   
        $response = new Response($respJson);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}
