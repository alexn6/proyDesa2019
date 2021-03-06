<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Categoria;

/**
 * Categorias controller
 * @Route("/api",name="api_")
 */
class CategoriaController extends AbstractFOSRestController
{
    /**
     * Lista de todos las competencias.
     * @Rest\Get("/categories"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function allCategories()
    {

        $repository=$this->getDoctrine()->getRepository(Categoria::class);
        $categories=$repository->findall();

        $categories = $this->get('serializer')->serialize($categories, 'json', [
        'circular_reference_handler' => function ($object) {
            return $object->getId();
        }
        ]);

        // pasamos a un array para procesarlo
        $array_comp = json_decode($categories, true);
        // cambiamos el objeto deporte por su nombre
        foreach ($array_comp as &$valor) {
           $valor['deporte'] = $valor['deporte']['nombre'];
        }
        // pasamos todo a json de nuevo
        $array_comp = json_encode($array_comp);

        $response = new Response($array_comp);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * Devuelve todas las categorias a las que le pertenece a un deporte
     * @Rest\Get("/categoriesBySport"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getCategoriesBySport(Request $request){
        $repository=$this->getDoctrine()->getRepository(Categoria::class);
      
        $respJson = (object) null;

        $id_deporte = $request->get('idDeporte');

        // vemos si recibimos algun parametro
        if(!empty($id_deporte)){
            $categories = $repository->findCategoriesBySport($id_deporte);
            $statusCode = Response::HTTP_OK;
        }
        else{
            $categories  = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
        }
        $categories = $this->get('serializer')->serialize($categories, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();}
            ]);
    
        // pasamos a un array para procesarlo
        $array_comp = json_decode($categories, true);
        // cambiamos el objeto deporte por su nombre
        foreach ($array_comp as &$valor) {
           $valor['deporte'] = $valor['deporte']['nombre'];
        }
        // pasamos todo a json de nuevo
        $array_comp = json_encode($array_comp);

        $response = new Response($array_comp);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
    
        return $response;
    }    


}