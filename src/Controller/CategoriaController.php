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

        $response = new Response($categories);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}