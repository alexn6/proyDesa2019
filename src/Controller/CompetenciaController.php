<?php

namespace App\Controller;

//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Competencia;

/**
 * Competencia controller
 * @Route("/api",name="api_")
 */
class CompetenciaController extends AbstractFOSRestController
{

  /**
     * Lista de todos las competencias.
     * @Rest\Get("/competitions"), defaults={"_format"="json"})
     * 
     * @return Response
     */
  public function allCompetition()
  {

    $repository=$this->getDoctrine()->getRepository(Competencia::class);
    $competitions=$repository->findall();

    $competitions = $this->get('serializer')->serialize($competitions, 'json', [
      'circular_reference_handler' => function ($object) {
        return $object->getId();
      },
      'ignored_attributes' => ['usuarioscompetencias', '__initializer__', '__cloner__', '__isInitialized__']
    ]);

    // Convert JSON string to Array
    $array_comp = json_decode($competitions, true);
    // var_dump($someArray);
    //var_dump($competitions);

    foreach ($array_comp as &$valor) {
      $valor['categoria']['deporte'] = $valor['categoria']['deporte']['nombre'];
    }

    $array_comp = json_encode($array_comp);

    // $response = new Response($competitions);
    $response = new Response($array_comp);
    $response->setStatusCode(Response::HTTP_OK);
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }

    /**
     * 
     * @Rest\Post("/existcompetition")
     * 
     * @return Response
     */
    public function existCompetition(Request $request){

        $existCompetition = true;
        $repository=$this->getDoctrine()->getRepository(Competencia::class);
  
        // $data = json_decode($request->getContent(),true);
        // var_dump($data);
        $nombreCompetencia = $request->get('competencia');
        // var_dump($nombreCompetencia);
        
        $competition = $repository->findOneBy(['nombre' => $nombreCompetencia]);
  
        if (!$competition) {
            $existCompetition = false;
        }
  
        $respJson = (object) null;
        $respJson->existe = $existCompetition;
  
        $respJson = json_encode($respJson);
  
        $response = new Response($respJson);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
  
        return $response;
    }

    // ########################################################
    // ################### funciones auxiliares ################
    
}