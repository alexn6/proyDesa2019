<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;    // para incorporar servicios rest

use App\Entity\Predio;
use App\Entity\PredioCompetencia;
use App\Entity\Competencia;

 /**
 * Predio controller
 * @Route("/api",name="api_")
 */
class PredioCompetenciaController extends AbstractFOSRestController
{
    /**
     * Devuelve todas los predios de una competencia
     * @Rest\Get("/grounds/competition"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getGroundsByCompetition(Request $request){      
        $respJson = (object) null;
        $statusCode;
        $grounds = null;

        $idCompetencia = $request->get('id');

        // vemos si recibimos algun parametro
        if(!empty($idCompetencia)){
            $repository = $this->getDoctrine()->getRepository(PredioCompetencia::class);
            $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);

            $competencia = $repositoryComp->find($idCompetencia);

            if($competencia == null){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Peticion mal formada. Faltan parametros.";
            }
            else{
                // buscamos los predios de la competencia
                $grounds = $repository->groundsByCompetetition($idCompetencia);
                $respJson = $grounds;
                $statusCode = Response::HTTP_OK;
            }
        }
        else{
            $grounds  = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Faltan parametros";
        }

        $respJson = json_encode($respJson);
        
        $response = new Response($respJson);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
    
        return $response;
    }
    
}