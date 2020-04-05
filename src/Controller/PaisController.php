<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Pais;



 /**
 * Pais controller
 * @Route("/api",name="api_")
 */
class PaisController extends AbstractFOSRestController{
    
    /**
     * Lista de todos las jueces.
     * @Rest\Get("/countries"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function allCountries()
    {
        $repository=$this->getDoctrine()->getRepository(Pais::class);
        $paises=$repository->findall();

        $paises = $this->get('serializer')->serialize($paises, 'json', [
        'circular_reference_handler' => function ($object) {
            return $object->getId();
        }
        ]);

        $response = new Response($paises);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
