<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
        $generos = array();
        // creamos los generos
        $genero1 = (object) null;
        $genero1->id = 1;
        $genero1->nombre = "MASCULINO";
        $genero2 = (object) null;
        $genero2->id = 2;
        $genero2->nombre = "FEMENINO";
        $genero3 = (object) null;
        $genero3->id = 3;
        $genero3->nombre = "MIXTO";

        // agregamos los generos
        array_push($generos, $genero1);
        array_push($generos, $genero2);
        array_push($generos, $genero3);

        // convertimos el objeto en un json
        $respJson = json_encode($generos);

        // siempre da ok la peticion
        $response = new Response($respJson);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}