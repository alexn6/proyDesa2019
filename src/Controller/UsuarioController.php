<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Usuario;
use App\Entity\UsuarioCompetencia;
use App\Entity\Campo;
use App\Entity\Competencia;
use App\Entity\Notification;
use App\Entity\Juez;
use App\Entity\JuezCompetencia;

use App\Utils\VerificationMail;
use App\Utils\MailManager;
use App\Utils\NotificationManager;
use App\Utils\Constant;

 /**
 * Usuario controller
 * @Route("/api",name="api_")
 */
class UsuarioController extends AbstractFOSRestController
{

  private $passwordEncoder;

  /**
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

  /**
     * Lista de todos los usuarios.
     * @Rest\Get("/users"), defaults={"_format"="json"})
     * 
     * @return Response
     */
  public function getUsers(){
    $repository=$this->getDoctrine()->getRepository(Usuario::class);
    $users=$repository->findall();

    // hacemos el string serializable , controlamos las autoreferencias
    $users = $this->get('serializer')->serialize($users, 'json', [
      'circular_reference_handler' => function ($object) {
        return $object->getId();
      },
      'ignored_attributes' => ['usuarioscompetencias', 'roles', 'password', 'pass', 'salt','token','notification']
    ]);

    $response = new Response($users);
    $response->setStatusCode(Response::HTTP_OK);
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }


  /**
     * Actualiza los datos de un usuario
     * @Rest\Put("/user"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function updateUser(Request $request){
      $respJson = (object) null;

      // vemos si existe un body
      if(!empty($request->getContent())){
        // recuperamos los datos del body y pasamos a un array
        $dataUserRequest = json_decode($request->getContent());      
        // buscamos el usuario
        $idUser = $dataUserRequest->id;
        $repository = $this->getDoctrine()->getRepository(Usuario::class);
        $user = $repository->find($idUser);

        // vemos si existe el usuario para actualizar sus datos
        if($user == NULL){
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Usuario inexistente";
        }
        else{
          $usuarioPropio = $user->getNombreUsuario();
          $correoPropio = $user->getCorreo();

          // los declaramos aca para usarlos tmb fuera de los if
          $userNombreUsuario = NULL;
          $userCorreo = NULL;
          
          // controlamos que el nombre de usuario y el correo este disponible
          if($dataUserRequest->usuario != $usuarioPropio){
            $userNombreUsuario = $repository->findOneBy(['nombreUsuario' => $dataUserRequest->usuario]);
          }
          if($dataUserRequest->correo != $correoPropio){
            $userCorreo = $repository->findOneBy(['correo' => $dataUserRequest->correo]);
          }

          // TODO: controlar que no sea su propio correo o usuario
          if(($userNombreUsuario == NULL)&&($userCorreo == NULL)){
            //actualizamos los datos del usuario
            $user->setNombre($dataUserRequest->nombre);
            $user->setApellido($dataUserRequest->apellido);
            $user->setCorreo($dataUserRequest->correo);
            $user->setNombreUsuario($dataUserRequest->usuario);
    
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
    
            $statusCode = Response::HTTP_OK;
            // TODO: devolver el usuario para la app android
            // $respJson->messaging = "Actualizacion exitosa";
            $respJson->id = $user->getId();
            $respJson->nombre = $user->getNombre();
            $respJson->apellido = $user->getApellido();
            $respJson->correo = $user->getCorreo();
            $respJson->usuario = $user->getNombreUsuario();
          }
          else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            if($userNombreUsuario != NULL){
              $respJson->messaging = "El nombre de usuario no esta disponible. Intente con otro.";
            }
            else{
              $respJson->messaging = "El correo no esta disponible. Intente con otro.";
            }
          }

        }
      }
      else{
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Peticion mal formada. Faltan parametros.";
      }
  
      $respJson = json_encode($respJson);
      $response = new Response($respJson);
      $response->headers->set('Content-Type', 'application/json');
      $response->setStatusCode($statusCode);
  
      return $response;
    }

    /**
     * Cambia al contraseña del usuario
     * @Rest\Put("/user/uppass"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function changePassUser(Request $request){
      $respJson = (object) null;

      // vemos si existe un body
      if(!empty($request->getContent())){
        // recuperamos los datos del body y pasamos a un array
        $dataUserRequest = json_decode($request->getContent());      
        // los declaramos aca para usarlos tmb fuera de los if
        $userNombreUsuario = NULL;
        $userCorreo = NULL;
        
        $repository = $this->getDoctrine()->getRepository(Usuario::class);
        // buscamos el usuario por su correo y nombre de usuario
        $userNombreUsuario = $repository->findOneBy(['nombreUsuario' => $dataUserRequest->usuario]);
        $userCorreo = $repository->findOneBy(['correo' => $dataUserRequest->usuario]);
        

        // vemos si existe el usuario para actualizar sus datos
        if(($userNombreUsuario == NULL)&&(($userCorreo == NULL))){
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Usuario inexistente";
        }
        else{
          if($userNombreUsuario != NULL){
            $usuario = $userNombreUsuario;
          }
          else{
            $usuario = $userCorreo;
          }

          // encriptamos la contraseña
          $passHash = $this->passwordEncoder->encodePassword($usuario, $dataUserRequest->pass);
          $usuario->setPass($passHash);
  
          $em = $this->getDoctrine()->getManager();
          $em->persist($usuario);
          $em->flush();
  
          $statusCode = Response::HTTP_OK;
          $respJson->messaging = "Contraseña cambiada con exito.";

        }
      }
      else{
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Peticion mal formada. Faltan parametros.";
      }
  
      $respJson = json_encode($respJson);
      $response = new Response($respJson);
      $response->headers->set('Content-Type', 'application/json');
      $response->setStatusCode($statusCode);
  
      return $response;
    }

  /**
     * Registra un nuevo usuario.
     * @Rest\Post("/user"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function create(Request $request){

      $respJson = (object) null;
      $statusCode;
      // vemos si existe un body
      if(!empty($request->getContent())){

        $repository=$this->getDoctrine()->getRepository(Usuario::class);
  
        // recuperamos los datos del body y pasamos a un array
        $dataUserRequest = json_decode($request->getContent());      
        // var_dump($dataUserRequest);

        $nombreUsuario = $dataUserRequest->usuario;
        $correo = $dataUserRequest->correo;
          
        // controlamos que el nombre de usuario este disponible
        $usuario = $repository->findOneBy(['nombreUsuario' => $nombreUsuario]);
        if($usuario){
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "El nombre de usuario esta en uso";
        }

        // controlamos que exista el correo
        $verificadorMail = new VerificationMail();
        $existEmail = $verificadorMail->verify($correo);
        if($existEmail){
          // controlamos que el correo no este en uso
          $usuario_correo = $repository->findOneBy(['correo' => $correo]);
          if($usuario_correo){
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "El correo esta en uso por una cuenta existente";
          }
        }
        else{
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Correo inexistente";
        }

  
        if((!$usuario)&&($existEmail)&&(!$usuario_correo)){

          $repositoryNotif = $this->getDoctrine()->getRepository(Notification::class);

          // creamos las notificaciones a seguir
          $notification = new Notification();
          $notification->setSeguidor(true);
          $notification->setCompetidor(true);
          $em = $this->getDoctrine()->getManager();
          $em->persist($notification);
          $em->flush();

          // creamos el usuario
          $usuarioCreate = new Usuario();
          $usuarioCreate->setNombre($dataUserRequest->nombre);
          $usuarioCreate->setApellido($dataUserRequest->apellido);
          $usuarioCreate->setNombreUsuario($nombreUsuario);
          $usuarioCreate->setCorreo($correo);
          $usuarioCreate->setPass($dataUserRequest->pass);
          $usuarioCreate->setToken($dataUserRequest->token);
          $usuarioCreate->setNotification($notification);
  
          // encriptamos la contraseña
          $passHash = $this->passwordEncoder->encodePassword($usuarioCreate, $usuarioCreate->getPass());
          $usuarioCreate->setPass($passHash);
  
          $em = $this->getDoctrine()->getManager();
          $em->persist($usuarioCreate);
          $em->flush();
  
          $statusCode = Response::HTTP_CREATED;
          $respJson->messaging = "Creacion exitosa";
        }
      }
      else{
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Peticion mal formada";
      }

      
      $respJson = json_encode($respJson);

      $response = new Response($respJson);
      $response->headers->set('Content-Type', 'application/json');
      $response->setStatusCode($statusCode);

      return $response;
  }

  /**
     * Controla el iniicio de sesion de un usuario
     * @Rest\Post("/user/singin"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function singin(Request $request){

      $respJson = (object) null;
      $statusCode;

      // vemos si existe un body
      if(!empty($request->getContent())){

        $repository=$this->getDoctrine()->getRepository(Usuario::class);
  
        // recuperamos los datos del body y pasamos a un array
        $dataUserRequest = json_decode($request->getContent());
        
          // recuperamos los datos del body
          $usuario = $dataUserRequest->usuario;
          $pass = $dataUserRequest->pass;
            
          // controlamos que el usuario exista
          $repositoryComp = $this->getDoctrine()->getRepository(Usuario::class);

          // buscamos el usuario por los 2 campos
          $nombreUsuarioDB = $repository->findOneBy(['nombreUsuario' => $usuario]);
          $correoDB = $repository->findOneBy(['correo' => $usuario]);

          // si no encontramos un usuario con el nombre de usuario pasamos a probar con el correo
          if(($nombreUsuarioDB == NULL) && ($correoDB == NULL)){
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Usuario inexistente";
          }
          else{
            // recuperamos el usuario
            if($nombreUsuarioDB != NULL){
              $usuarioDB = $nombreUsuarioDB;
            }
            else{
              $usuarioDB = $correoDB;
            }
            // controlamos si la contraseña es correcta
            if($this->passwordEncoder->isPasswordValid($usuarioDB, $pass)){
              $statusCode = Response::HTTP_OK;
              $respJson = (object) null;
              $respJson->id = $usuarioDB->getId();
              $respJson->nombre = $usuarioDB->getNombre();
              $respJson->apellido = $usuarioDB->getApellido();
              $respJson->correo = $usuarioDB->getCorreo();
              $respJson->usuario = $usuarioDB->getNombreUsuario();
            }
            else{
              $statusCode = Response::HTTP_BAD_REQUEST;
              $respJson->messaging = "La contraseña no es correcta";
            }
          }
      }
      else{
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Peticion mal formada";
      }
      
      $respJson = json_encode($respJson);

      $response = new Response($respJson);
      $response->headers->set('Content-Type', 'application/json');
      $response->setStatusCode($statusCode);

      return $response;
  }

  /**
     * Resetea la contraseña del usuario
     * @Rest\Post("/user/recovery"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function recoveryPassMail(Request $request){

      $respJson = (object) null;
      $statusCode;

      // vemos si existe un body
      if(!empty($request->getContent())){

        $repository=$this->getDoctrine()->getRepository(Usuario::class);
  
        // recuperamos los datos del body y pasamos a un array
        $dataUserRequest = json_decode($request->getContent());
        
        // los declaramos aca para usarlos tmb fuera de los if
        $userNombreUsuario = NULL;
        $userCorreo = NULL;
        
        // buscamos el usuario por su correo y nombre de usuario
        $userNombreUsuario = $repository->findOneBy(['nombreUsuario' => $dataUserRequest->usuario]);
        $userCorreo = $repository->findOneBy(['correo' => $dataUserRequest->usuario]);
          
        // controlamos que el usuario exista
        $repositoryComp = $this->getDoctrine()->getRepository(Usuario::class);

        // si no encontramos un usuario con el nombre de usuario pasamos a probar con el correo
        if(($userNombreUsuario == NULL)&&($userCorreo == NULL)){
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Nombre de usaurio o correo no registrado";
        }
        else{
          if($userNombreUsuario != NULL){
            $usuarioRegistrado = $userNombreUsuario;
          }
          else{
            $usuarioRegistrado = $userCorreo;
          }
          $newResetPass = mt_rand(100000,999999);
          // Enviamos el cod de verificacion
          $this->sendCodVerification($newResetPass, $usuarioRegistrado->getCorreo());
          // encriptamos la contraseña
          $passHash = $this->passwordEncoder->encodePassword($usuarioRegistrado, $newResetPass);
          $usuarioRegistrado->setPass($passHash);
  
          $em = $this->getDoctrine()->getManager();
          $em->persist($usuarioRegistrado);
          $em->flush();

          $statusCode = Response::HTTP_OK;
          $respJson->messaging = "Se envio un codigo de verificacion a su direccion de correo de su cuenta.";
        }
      }
      else{
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Peticion mal formada";
      }
      
      $respJson = json_encode($respJson);

      $response = new Response($respJson);
      $response->headers->set('Content-Type', 'application/json');
      $response->setStatusCode($statusCode);

      return $response;
  }

  /**
   * Actualiza el rol de usuario_competencia
   * @Rest\Put("/user-token"), defaults={"_format"="json"})
   * 
   * @return Response
   */
  public function updateToken(Request $request){

    $respJson = (object) null;
    $statusCode;

    if(!empty($request->getContent())){

      // recuperamos los datos del body y pasamos a un array
      $dataRequest = json_decode($request->getContent());

      if((!empty($dataRequest->nombreUsuario))&&(!empty($dataRequest->token))){
        // vemos si existen los datos necesarios
        $nombreUsuario = $dataRequest->nombreUsuario;
        $new_token = $dataRequest->token;
        
        // buscamos el usuario
        $repository=$this->getDoctrine()->getRepository(Usuario::class);
        $usuario = $repository->findOneBy(['nombreUsuario' => $nombreUsuario]);

        if($usuario != NULL){
          // TODO: incluir esto de manera correcta
          // $tokenViejo = $usuario->getToken();
          // if($tokenViejo != $new_token){
          //   // vemos si existia un token
          //   if($tokenViejo != NULL){
          //     $this->updateSubscriptionsTopics($tokenViejo, $new_token);
          //   }
          //   $em = $this->getDoctrine()->getManager();
          //   $usuario->setToken($new_token);
          //   $em->flush();
          // }
          
          $em = $this->getDoctrine()->getManager();
          $usuario->setToken($new_token);
          $em->flush();
  
          $respJson->messaging = "Actualizacion realizada";
        }
        else{
          $respJson->messaging = "El usuario no existe";
        }
        $statusCode = Response::HTTP_OK;
      }
      else{
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Solicitud mal formada";
      }
      
    }
    else{
      $statusCode = Response::HTTP_BAD_REQUEST;
      $respJson->messaging = "Solicitud mal formada";
    }

    
    $respJson = json_encode($respJson);

    $response = new Response($respJson);
    $response->headers->set('Content-Type', 'application/json');
    $response->setStatusCode($statusCode);

    return $response;
  }

  /**
   * Recupera todos los datos de determinada competencia de un usuario
   * para poder trabajar de manera offline
   * @Rest\Post("/user/off"), defaults={"_format"="json"})
   * 
   * @return Response
   */
  public function dataUserOffline(Request $request){

    $respJson = (object) null;
    $statusCode;

    if(!empty($request->getContent())){

      // recuperamos los datos del body y pasamos a un array
      $dataRequest = json_decode($request->getContent());

      if((isset($dataRequest->idUsuario))&&(isset($dataRequest->idCompetencia))){
        // recuperamos los datos
        $idUsuario = $dataRequest->idUsuario;
        $idCompetencia = $dataRequest->idCompetencia;
        
        // buscamos el usuario
        $repository = $this->getDoctrine()->getRepository(Usuario::class);
        $usuario = $repository->find($idUsuario);

        if($usuario == NULL){
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "El usuario no existe";
        }
        else{
          // buscamos el usuario
          $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
          $competencia = $repositoryComp->find($idCompetencia);

          if($competencia == NULL){
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "La competencia no existe";
          }
          else{
            // buscamos el usuario
            $relation = $repository->relationUserCompetition($idUsuario, $idCompetencia);

            if($relation == NULL){
              $statusCode = Response::HTTP_BAD_REQUEST;
              $respJson->messaging = "El usuario no tiene relacion con la competencia.";
            }
            else{
              $dataCompetitionOffline = $repositoryComp->dataOffline($idUsuario, $idCompetencia);
              $dataCompetitionOffline = $this->get('serializer')->serialize($dataCompetitionOffline, 'json', [
                'circular_reference_handler' => function ($object) {
                  return $object->getId();
                }]);
              // pasamos los datos a un array para poder trabajarlos
              $dataCompetitionOffline = json_decode($dataCompetitionOffline, true);
              $dataCompetition = $dataCompetitionOffline[0];
              // le asignamos la fase de la eliminatoria
              $newType = $this->getPhase($dataCompetition['id'], $dataCompetition['organizacion']);
              $dataCompetition['organizacion'] = $newType;
              $dataCompetition['fecha_ini'] = substr($dataCompetition['fecha_ini'], 0, -15);

              $repositoryUserComp = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
              $competitors = $repositoryUserComp->competidoresByCompetenciaOffline($idCompetencia);

              $repositoryField = $this->getDoctrine()->getRepository(Campo::class);
              $fileds = $repositoryField->findFielsByCompetition($idCompetencia);
              if($fileds != null){
                $fileds = $this->get('serializer')->serialize($fileds, 'json', [
                  'circular_reference_handler' => function ($object) {
                    return $object->getId();
                  },
                  'ignored_attributes' => ['competencia','__initializer__', '__cloner__', '__isInitialized__']
                  ]);
  
                $fileds = json_decode($fileds, true);
                // le agregamos el id de la competencia
                for ($i=0; $i < count($fileds); $i++) {
                  $fileds[$i]["idCompetencia"] = $idCompetencia;
                }
              }

              $repositoryJuezComp = $this->getDoctrine()->getRepository(JuezCompetencia::class);
              $judges = $repositoryJuezComp->refereesByCompetetition($idCompetencia);
              if($judges != null){
                $judges = $this->get('serializer')->serialize($judges, 'json', [
                  'circular_reference_handler' => function ($object) {
                    return $object->getId();
                  },
                  'ignored_attributes' => ['competencia','__initializer__', '__cloner__', '__isInitialized__']
                  ]);
  
                $judges = json_decode($judges, true);
                // le agregamos el id de la competencia
                for ($i=0; $i < count($judges); $i++) {
                  $judges[$i]["idCompetencia"] = $idCompetencia;
                }
              }
              else{
                $judges = null;
              }
              
              $inscription = null;
              // recuperamos la inscripcion
              if($competencia->getInscripcion() != null){
                  $inscription = $competencia->getInscripcion();
                  $inscription = $this->get('serializer')->serialize($inscription, 'json', [
                      'circular_reference_handler' => function ($object) {
                          return $object->getId();
                      },
                      'ignored_attributes' => ['competencia', '__initializer__', '__cloner__', '__isInitialized__']
                  ]);
                  $inscription = json_decode($inscription, true);
                  // cambiamos las fechas a formato resumido
                  $inscription['fechaIni'] = substr($inscription['fechaIni'], 0, -15);
                  $inscription['fechaCierre'] = substr($inscription['fechaCierre'], 0, -15);
                  $inscription['idCompetencia'] = $idCompetencia;
              }
              
              // recuperamos las fases creadas de la competencia
              $repositoryCompetencia = $this->getDoctrine()->getRepository(Competencia::class);
              $fasesDb = $repositoryCompetencia->phasesCreated($idCompetencia);
              $arrayFases = array();

              foreach($fasesDb as $fase){
                array_push($arrayFases, $fase["fase"]);
              }
              
              $respJson->competencia = $dataCompetition;
              $respJson->competidores = $competitors;
              $respJson->fields = $fileds;
              $respJson->judges = $judges;
              $respJson->inscription = $inscription;
              $respJson->fases = $arrayFases;

              $statusCode = Response::HTTP_OK;
            }
          }
        }
      }
      else{
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Solicitud mal formada";
      }
      
    }
    else{
      $statusCode = Response::HTTP_BAD_REQUEST;
      $respJson->messaging = "Solicitud mal formada";
    }

    
    $respJson = json_encode($respJson);

    $response = new Response($respJson);
    $response->headers->set('Content-Type', 'application/json');
    $response->setStatusCode($statusCode);

    return $response;
  }

  /**
   * Devuelve la configuracion de notificaciones del usaurio
   * @Rest\Get("/user-notif/config"), defaults={"_format"="json"})
   * 
   * @return Response
   */
  public function getConfigNotif(Request $request){

    $respJson = (object) null;
    $statusCode;

    if(!empty($request->get('idUsuario'))){        
        // buscamos el usuario
        $repository = $this->getDoctrine()->getRepository(Usuario::class);
        $usuario = $repository->find($request->get('idUsuario'));

        if($usuario != NULL){
          $config = (object) null;
          $config->seguidor = $usuario->getNotification()->getSeguidor();
          $config->competidor = $usuario->getNotification()->getCompetidor();
          $respJson = $config;
          $statusCode = Response::HTTP_OK;
        }
        else{
          $respJson->messaging = "El usuario no existe";
          $statusCode = Response::HTTP_NO_CONTENT;
        }
    }
    else{
      $statusCode = Response::HTTP_BAD_REQUEST;
      $respJson->messaging = "Solicitud mal formada. Faltan parametros.";
    }

    
    $respJson = json_encode($respJson);

    $response = new Response($respJson);
    $response->headers->set('Content-Type', 'application/json');
    $response->setStatusCode($statusCode);

    return $response;
  }
  
  /**
   * Actualiza la configuracion del tipo de competencia de las cuales
   * quiere recibir notificaciones
   * @Rest\Post("/user-notif"), defaults={"_format"="json"})
   * 
   * @return Response
   */
  public function updateConfigNotif(Request $request){

    $respJson = (object) null;
    $statusCode;

    if(!empty($request->getContent())){
      // recuperamos los datos del body y pasamos a un array
      $dataRequest = json_decode($request->getContent());

      if((isset($dataRequest->idUsuario))&&(isset($dataRequest->seguidor))&&(isset($dataRequest->competidor))){
        // vemos si existen los datos necesarios
        $idUsuario = $dataRequest->idUsuario;
        $enable_notif_seguidor = $dataRequest->seguidor;
        $enable_notif_competidor = $dataRequest->competidor;
        
        // buscamos el usuario
        $repository = $this->getDoctrine()->getRepository(Usuario::class);
        $usuario = $repository->find($idUsuario);

        if($usuario != NULL){ 
          $em = $this->getDoctrine()->getManager();
          
          if($enable_notif_seguidor == 'true'){
            $enable_notif_seguidor = 1;
          }
          else{
            $enable_notif_seguidor = 0;
          }
          if($enable_notif_competidor == 'true'){
            $enable_notif_competidor = 1;
          }
          else{
            $enable_notif_competidor = 0;
          }
                    
          // si cambia la config anterior
          if($usuario->getNotification()->getSeguidor() != $enable_notif_seguidor){
            // vemos si subscribimos o desusbcribimos al usuario de las competencias
            $this->updateSubscriptions($usuario, $enable_notif_seguidor, Constant::ROL_SEGUIDOR);
            $usuario->getNotification()->setSeguidor($enable_notif_seguidor);
          }

          if($usuario->getNotification()->getCompetidor() != $enable_notif_competidor){
            // vemos si subscribimos o desusbcribimos al usuario de las competencias
            $this->updateSubscriptions($usuario, $enable_notif_competidor, Constant::ROL_COMPETIDOR);
            $usuario->getNotification()->setCompetidor($enable_notif_competidor);
          }

          $em->flush();
  
          $respJson->messaging = "Actualizacion realizada.";
        }
        else{
          $respJson->messaging = "El usuario no existe";
          $statusCode = Response::HTTP_NO_CONTENT;
        }
        $statusCode = Response::HTTP_OK;
      }
      else{
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Solicitud mal formada. Faltan parametros.";
      }
      
    }
    else{
      $statusCode = Response::HTTP_BAD_REQUEST;
      $respJson->messaging = "Solicitud mal formada. Faltan parametros.";
    }

    
    $respJson = json_encode($respJson);

    $response = new Response($respJson);
    $response->headers->set('Content-Type', 'application/json');
    $response->setStatusCode($statusCode);

    return $response;
  }

  /**
     * Lista de todos los usuarios que contengan el nombre de usuario pasado por parametro.
     * @Rest\Get("/users/getUsersByUsername"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getUsersByUsername(Request $request){
      $respJson = (object) null;
      $users = null;
      $statusCode;
      $repository=$this->getDoctrine()->getRepository(Usuario::class);

      if(empty($request->get('username'))){
        $respJson->success = false;
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Peticion mal formada. Faltan parametros.";
        
      }else{
        $username = $request->get('username');
        $statusCode = Response::HTTP_OK;
      
        $users=$repository->getUsersByUsername($username);
  
        // hacemos el string serializable , controlamos las autoreferencias
        $users = $this->get('serializer')->serialize($users, 'json', [
          'circular_reference_handler' => function ($object) {
            return $object->getId();
          },
          'ignored_attributes' => ['usuarioscompetencias', 'token', 'roles', 'password', 'pass', 'salt']
        ]);
        $respJson = json_decode($users);
      }

      $respJson = json_encode($respJson);
       
      $response = new Response($respJson);
      $response->setStatusCode($statusCode);
      $response->headers->set('Content-Type', 'application/json');
  
      return $response;
    }

    // ##########################################################################################
    // ############################## FUNCIONES PRIVADAS ########################################

    // Devuelve el tipo de organizacion y la fase, si es una eliminatoria
  private function getPhase($idCompetition, $typeOrg){
    if(strpos($typeOrg, 'Eliminatorias') !== false){
      // vamos a buscar la fase en la que se encuentra la competencia
      $repository = $this->getDoctrine()->getRepository(Competencia::class);
      $competitionAux = $repository->find($idCompetition);
      $fase = $competitionAux->getFase();
      $faseCompetition;

      if($fase == 1){
        $faseCompetition = "Final";
      }
      if($fase == 2){
        $faseCompetition = "Semifinal";
      }
      if($fase == 3){
        $faseCompetition = "4º Final";
      }
      if($fase == 4){
        $faseCompetition = "8º Final";
      }
      if($fase == 5){
        $faseCompetition = "16º Final";
      }
      if($fase == 6){
        $faseCompetition = "32º Final";
      }
      if($fase == 7){
        $faseCompetition = "64º Final";
      }
      
      return $typeOrg." - ".$faseCompetition;
    }
    
    return $typeOrg;
  }

    private function getArrayNameCompetitions($arrayCompetions){
      $arrayNames = array();
      foreach ($arrayCompetions as &$competition) {
        array_push($arrayNames, $competition['nombre']);
      }

      return $arrayNames;
    }

    // actualiza el estado de todas las subscripciones de las competencias de las
    // que forma parte un usuario, segun su rol
    private function updateSubscriptions($user, $enable, $nameRol){
      $repository = $this->getDoctrine()->getRepository(Usuario::class);
      $arrayTopics;
      // vemos segun el rol los topicos a los que etsa subscrito
      if($nameRol == Constant::ROL_SEGUIDOR){
        $arrayTopics = $repository->namesCompetitionsFollow($user->getId());
      }
      //var_dump($nameRol);
      if($nameRol == Constant::ROL_COMPETIDOR){
        $arrayTopics = $repository->namesCompetitionsCompete($user->getId());
      }
      // var_dump($arrayTopics);
      // var_dump($enable);
      $arrayTopics = $this->getArrayNameCompetitions($arrayTopics);
      // agregamos el rol a cada competencia
      for ($i=0; $i < count($arrayTopics); $i++) {
        $arrayTopics[$i] = $arrayTopics[$i].'-'.$nameRol;
      }

      // dependiendo del estado de habilitado subscribimos o desubscribimos
      if($enable){
        NotificationManager::getInstance()->susbcribeAllTopic($user->getToken(), $arrayTopics);
      }
      if(!$enable){
        NotificationManager::getInstance()->unsusbcribeAllTopic($user->getToken());
      }
      
    }

    private function sendCodVerification($codVerification, $mailDestino){
      $msg = 'Tu codigo de verificacion es '.$codVerification.'. No lo compartas.';

      // MailManager::getInstance()->sendMail(Constant::APP_MOVIL_NAME, Constant::SWFMAILER_SERVER_SMTP_USER, $mailDestino, $msg);
      MailManager::getInstance()->sendMail(Constant::APP_MOVIL_NAME, $mailDestino, $msg);
    }

    // mantenemos las subscripciones del token anterior
    // private function updateSubscriptionsTopics($tokenViejo, $new_token){

    //   // recuperamos las subscripciones a los topicos
    //   $subscriptions = NotificationManager::getInstance()->getTopicsSusbcribed($tokenViejo);

    //   // desubscribimos al token viejo de los topicos
    //   foreach ($subscriptions as $subscription) {
    //       // echo "{$subscription->registrationToken()} fue subscrito a {$subscription->topic()}\n";
    //       //echo "Fue subscrito a {$subscription->topic()}\n";
    //       NotificationManager::getInstance()->unsubscribeTopic($subscription->topic(), $tokenViejo);
    //   }

    //   // subscribimos el token nuevo a los topicos que estaba usbscripto el token viejo
    //   foreach ($subscriptions as $subscription) {
    //     // echo "{$subscription->registrationToken()} fue subscrito a {$subscription->topic()}\n";
    //     NotificationManager::getInstance()->subscribeTopic($subscription->topic(), $new_token);
    //   }
    // }

}