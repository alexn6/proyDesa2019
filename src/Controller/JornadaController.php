<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Jornada;

/**
 * Categorias controller
 * @Route("/api",name="api_")
 */
class JornadaController extends AbstractFOSRestController
{
    // #############################################################
    // ############# metodos publicos #############################
    public function save($newJornada){
        //var_dump($newJornada);
        $em = $this->getDoctrine()->getManager();
        $em->persist($newJornada);
        $em->flush();
    }
}