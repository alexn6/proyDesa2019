<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Utils\GeneratorEncuentro;

 /**
 * Usuario controller
 * @Route("/api/generator",name="api_")
 */
class GeneradorEncuentroController extends AbstractFOSRestController
{

    private $generator;

    
    public function __construct(GeneratorEncuentro $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Generacion de encuentros LIGA SINGLE
     * @Rest\Post("/league-single"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function generateMatchesLeagueSingle(Request $request){

        $respJson = (object) null;
        $statusCode;

        // vemos si existe un body
        if(!empty($request->getContent())){
            // recuperamos los datos del body y pasamos a un array
            $competitorsBody = json_decode($request->getContent(), true);
            // var_dump($competitorsBody);

            $matchesCompetition = $this->generator::ligaSingle($competitorsBody);
            //var_dump($matchesCompetition);

            $statusCode = Response::HTTP_OK;
            $respJson->matches = $matchesCompetition;
            $respJson->messaging = "Se recibio el body";
        }
        else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->matches = NULL;
            $respJson->messaging = "Peticion mal formada";
        }
    
          
        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);

        return $response;
    }

    /**
     * Generacion de encuentros LIGA SINGLE
     * @Rest\Post("/league-double"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function generateMatchesLeagueDouble(Request $request){

        $respJson = (object) null;
        $statusCode;

        // vemos si existe un body
        if(!empty($request->getContent())){
            // recuperamos los datos del body y pasamos a un array
            $competitorsBody = json_decode($request->getContent(), true);
            // var_dump($competitorsBody);

            $matchesCompetition = $this->generator::ligaDouble($competitorsBody);
            //var_dump($matchesCompetition);

            $statusCode = Response::HTTP_OK;
            $respJson->matches = $matchesCompetition;
            $respJson->messaging = "Se recibio el body";
        }
        else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->matches = NULL;
            $respJson->messaging = "Peticion mal formada";
        }
    
          
        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);

        return $response;
    }


    // private function getCompetitors($competitorsJson){
    //     $competitors = array();
    //     foreach ($competitorsJson as $k=>$v){
    //         array_push($competitors, $v);
    //     }
    //     return $competitors;
    // }
}