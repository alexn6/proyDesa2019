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
    static private $enumGeneros = null;

    /**
     * Lista de todos las competencias.
     * @Rest\Get("/genders"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function allGenders()
    {

        $generosEnum = $this->getGenerosEnum();
        $generos = array();

        foreach ($generosEnum as $val)
        {
            $genero = (object) null;
            $genero->nombre = $val;
            array_push($generos, $genero);
        }

        // convertimos el objeto en un json
        $respJson = json_encode($generos);

        // siempre da ok la peticion
        $response = new Response($respJson);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    

    private function getGenerosEnum()
    {
        if (self::$enumGeneros == null)
        {
            self::$enumGeneros = array ();
            $oClass = new \ReflectionClass('App\Utils\Constant');
            $classConstants = $oClass->getConstants();
            $constantPrefix = "GENERO";
            foreach ($classConstants as $key => $val)
            {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix)
                {
                array_push(self::$enumGeneros, $val);
                }
            }
        }
        return self::$enumGeneros;
    }
}