<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\UsuarioCompetencia;

 /**
 * Participante controller
 * @Route("/api",name="api_")
 */
class ParticipanteController extends AbstractFOSRestController
{
    /**
     * Devuelve toda la info de la tabla usuariocompetencia
     * @Rest\Get("/competitors"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getCompetitors(){
        $repository=$this->getDoctrine()->getRepository(UsuarioCompetencia::class);
        $competitors=$repository->findall();

        //var_dump($competitors);

        // hacemos el string serializable , controlamos las autoreferencias
        $competitors = $this->get('serializer')->serialize($competitors, 'json', [
            'circular_reference_handler' => function ($object) {
              return $object->getId();
            }
          ]);

        $response = new Response($competitors);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}