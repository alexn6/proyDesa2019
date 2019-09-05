<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class testControllerSPT extends AbstractController
{
  /**
   * name es solo para sermapeada en el archivo de config
  * @Route("/testSrvProy", name="testService")
  */
  public function respTestSrvTorneos()
  {
    $response = new JsonResponse(array('Funciona' => 'al toque'));
    //$response = new Response('<html><body>Lucky number: Resp del controller </body></html>');
    return $response;
  }
}