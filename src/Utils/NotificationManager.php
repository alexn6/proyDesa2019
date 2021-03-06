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
        //echo 'Construyendo el admin de notificaciones..'.PHP_EOL;

        // vinculamos el proyecto a la cuenta de servicio
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
    public function notificationSpecificDevices($tokenDevice, $notification){
        if(self::$manager === null){
            echo 'No se cuenta con un NotificationManager';
            return;
        }
        
        $message = CloudMessage::withTarget('token', $tokenDevice)
            ->withNotification($notification);
    
        try {
            // $myAdminsdkFirebase->validate($message);
            self::$manager->validate($message);
        } catch (InvalidMessage $e) {
            print_r($e->errors());
        }
    
        // $myAdminsdkFirebase->send($message);
        self::$manager->send($message);
    }

    // Envia una notificacion con datos al token recibido
    public function notificationSpecificDevicesWithData($tokenDevice, $notification, $data){
        if(self::$manager === null){
            // echo 'No se cuenta con un NotificationManager';
            return;
        }
        
        $message = CloudMessage::withTarget('token', $tokenDevice)
            ->withNotification($notification)
            ->withData($data);
    
        try {
            self::$manager->validate($message);
        } catch (InvalidMessage $e) {
            print_r($e->errors());
        }
    
        self::$manager->send($message);
    }

    // Envia una notificacion a un cjto de dispositivos (max = 500)
    public function notificationMultipleDevices($deviceTokens, $notification){
        $message = CloudMessage::new()
            ->withNotification($notification);
        
        $sendReport = self::$manager->sendMulticast($message, $deviceTokens);

        // echo 'Successful sends: '.$sendReport->successes()->count().PHP_EOL;
        // echo 'Failed sends: '.$sendReport->failures()->count().PHP_EOL;

        if ($sendReport->hasFailures()) {
            foreach ($sendReport->failures()->getItems() as $failure) {
                echo $sendReport->error()->getMessage().PHP_EOL;
            }
        }
    }

    // Envia una notificacion con datos a un cjto de dispositivos (max = 500)
    public function notificationMultipleDevicesWithData($deviceTokens, $notification, $data){
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($data);;
        
        $sendReport = self::$manager->sendMulticast($message, $deviceTokens);

        // echo 'Successful sends: '.$sendReport->successes()->count().PHP_EOL;
        // echo 'Failed sends: '.$sendReport->failures()->count().PHP_EOL;

        if ($sendReport->hasFailures()) {
            foreach ($sendReport->failures()->getItems() as $failure) {
                echo $sendReport->error()->getMessage().PHP_EOL;
            }
        }
    }

    // Envia una notificacion al topico especificado
    public function notificationToTopic($topic, $notification){
        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification($notification)
        ;

        self::$manager->send($message);
    }

    // Envia una notificacion con datos al topico especificado
    public function notificationToTopicWithData($topic, $notification, $data){
        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification($notification)
            ->withData($data);
        ;

        self::$manager->send($message);
    }

    public function subscribeTopic($topic, $token){
        self::$manager->subscribeToTopic($topic, $token);
    }

    public function unsubscribeTopic($topic, $token){
        self::$manager->unsubscribeFromTopic($topic, $token);
    }

    // subscribe al token a la lista de topicos recibidos
    public function susbcribeAllTopic($token, $arrayTopics){
        for ($i=0; $i < count($arrayTopics); $i++) {
            $topic = $arrayTopics[$i];
            $topic = str_replace(' ', '', $topic);
            self::$manager->subscribeToTopic($topic, $token);
        }
    }

    // desubscribe al token a la lista de topicos recibidos
    public function unsusbcribeAllTopic($token){
        $appInstance = self::$manager->getAppInstance($token);
        $subscriptions = $appInstance->topicSubscriptions();
        //var_dump(count($subscriptions));

        foreach ($subscriptions as $subscription) {
            self::$manager->unsubscribeFromTopic($subscription->topic(), $token);
        }
    }

    // public function validToken($token){
    //     $factory = new Factory();
    //     $auth = $factory->createAuth();

    //     try {
    //         $verifiedIdToken = $auth->verifyIdToken($token);
    //     } catch (\InvalidArgumentException $e) {
    //         echo 'TOKEN INVALIDO. '.$e->getMessage();
    //         return false;
    //     } catch (InvalidToken $e) {
    //         echo 'TOKEN INVALIDO. '.$e->getMessage();
    //         return false;
    //     }

    //     return true;
    //     // $uid = $verifiedIdToken->getClaim('sub');
    //     // $user = $auth->getUser($uid);
    // }

    // devuelve todas las subscripciones registradas de un token
    public function getTopicsSusbcribed($token){
        $appInstance = self::$manager->getAppInstance($token);
        /** @var \Kreait\Firebase\Messaging\TopicSubscriptions $subscriptions */
        $subscriptions = $appInstance->topicSubscriptions();

        return $subscriptions;
    }

}