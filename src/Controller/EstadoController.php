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
class EstadoController extends AbstractFOSRestController
{
    static private $enumEstados = null;

    /**
     * Lista de todos los estados de una competencia.
     * @Rest\Get("/competition/status"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function allStatusCompetitions()
    {

        $estadosEnum = $this->getEstadoEnum();
        $estados = array();

        foreach ($estadosEnum as $val)
        {
            $estado = (object) null;
            $estado->nombre = $val;
            array_push($estados, $estado);
        }

        // convertimos el objeto en un json
        $respJson = json_encode($estados);

        // siempre da ok la peticion
        $response = new Response($respJson);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }



    private function getEstadoEnum()
    {
        if (self::$enumEstados == null)
        {
            self::$enumEstados = array ();
            $oClass = new \ReflectionClass('App\Utils\Constant');
            $classConstants = $oClass->getConstants();
            $constantPrefix = "ESTADO";
            foreach ($classConstants as $key => $val)
            {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix)
                {
                array_push(self::$enumEstados, $val);
                }
            }
        }
        return self::$enumEstados;
    }
}