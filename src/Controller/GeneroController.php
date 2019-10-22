<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Competencia;

/**
 * Genero controller
 * @Route("/api",name="api_")
 */
class GeneroController extends AbstractFOSRestController
{
    /**
     * Lista de todos las competencias.
     * @Rest\Get("/genders"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function allGenders()
    {
        $generos = Competencia::getGenerosEnum();

        // convertimos el objeto en un json
        $respJson = json_encode($generos);

        // siempre da ok la peticion
        $response = new Response($respJson);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}