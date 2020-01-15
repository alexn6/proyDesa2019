<?php

namespace App\Utils;

require '/var/www/html/proyDesa2019/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

use Kreait\Firebase\Exception\Messaging\InvalidMessage;

// administrador de notificaciones - SINGLETON
class NotificationManager
{
    private static $instance;
    private static $manager;

    private function __construct()
    {
        echo 'Construyendo el admin de notificaciones..'.PHP_EOL;

        // vinculamos el proyecto a la cuenta de servicio
        // $factory = (new Factory())
        // ->withServiceAccount('/var/www/html/proyDesa2019/config/proyectotorneosfcm-firebase-adminsdk-account.json');
        $factory = new Factory();
        // creamos los permisos necesarios
        $auth = $factory->createAuth();
        // creamos el admin con mlos permisos y cuenta de servicio especificados
        self::$manager = $factory->createMessaging();
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // Envia un mensaje al token recibido
    public function notificationSpecificDevices($tokenDevice){
        //$myAdminsdkFirebase = self::$instance;

        if(self::$manager === null){
            echo 'No se cuenta con un NotificationManager';
            return;
        }
        
        $message = CloudMessage::withTarget('token', $tokenDevice)
            ->withNotification(Notification::create('Title', 'Body'))
            ->withData(['key' => 'Algun dato']);
    
        // $message = CloudMessage::fromArray([
        //     'token'=> 'dx3sViEPObw:APA91bHxTYWG4RSpu3Tza86_nVp2vXEnKPhZXzct-70-GoO1VdgT2Jbl2slpnC2NLLytEo7qmcuaH_jrPE6z7Xr5u4kCpoRJ0muKzu_HVThj_tzsTha-l2WmBNBXQ8vnglbmCIbwHhAy',
        //     'notification' => [/* Notification data as array */], // optional
        //     'data' => ['key' => 'Nuevo dato'], // optional
        // ]);
    
        try {
            // $myAdminsdkFirebase->validate($message);
            self::$manager->validate($message);
        } catch (InvalidMessage $e) {
            print_r($e->errors());
        }
    
        // $myAdminsdkFirebase->send($message);
        self::$manager->send($message);
    }

    // Envia una notificacion al topico especificado
    function notificationToTopic($topic, $msg){
        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification(Notification::create('TitleTopic', 'BodyTopic')) // optional
            ->withData(['key' => 'Algun dato']) // optional
        ;

        // $message = CloudMessage::fromArray([
        //     'topic' => $topic,
        //     'notification' => ['TitleTopic', 'BodyTopic'], // optional
        //     'data' => [/* data array */], // optional
        // ]);

        self::$manager->send($message);
    }
}