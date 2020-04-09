<?php

// namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Utils\NotificationManager;

use Kreait\Firebase\Messaging\Notification;
use App\Utils\Constant;

class TopicTokenTest extends TestCase{

    public static $token1 = 'cN86NyUL5Xk:APA91bGoW1jJ_rgcREdVLJynG3fK1_QlQ8Dk6djHsBfs4Ao7QytSSAeJed152On6yHHKRPKhWtW5NuypSgYe-hJ9IH4cRJOUDyYYBHkuU1WBd_XL6Om3GJPYum3ndJHQsjQmV8K5tpFG';
    public static $topic1 = "topico1";
    public static $topic2 = "topico2";
    public static $topic3 = "topico3";

    // tener en cuenta la demora del registro de subscripcion
    // tal vez con una promesa o algo asi se podria controlar esto

    // public function testSubscribe(){
    //     $topic = "TorneodePrueba";
    //     $token = 'cIQwwCHfZ1k:APA91bHodW8J9KXaVmbLCSMbkdjnmh0V2q3YzHfbsY6C2XBMQ8lp-18V-tbbuyAQm9s87jYn9-9tp4ijvnkysWopT6uMx3WhGhf-3gQMy_OlaDkwwWvJSSKZb5sThgyxpQIKfArBKL9w';
    //     // suscribimos al token a 3 topicos
    //     NotificationManager::getInstance()->subscribeTopic($topic, $token);

    //     $this->assertEquals(0, 0);
    // }

    // recuperamos todos los topicos a los que se subscribe un token (instancia)
    // public function testMySubscriptions(){

    //     // suscribimos al token a 3 topicos
    //     NotificationManager::getInstance()->subscribeTopic(self::$topic1, self::$token1);
    //     NotificationManager::getInstance()->subscribeTopic(self::$topic2, self::$token1);
    //     NotificationManager::getInstance()->subscribeTopic(self::$topic3, self::$token1);

    //     $subscriptions = NotificationManager::getInstance()->getTopicsSusbcribed(self::$token1);

    //     foreach ($subscriptions as $subscription) {
    //         // echo "{$subscription->registrationToken()} fue subscrito a {$subscription->topic()}\n";
    //         echo "Fue subscrito a {$subscription->topic()}\n";
    //     }

    //     $this->assertEquals(3, count($subscriptions));
    // }

    // // recuperamos todos los topicos a los que se subscribe un token (instancia)
    // public function testDesubscriptions(){

    //     // desuscribimos al token a 3 topicos
    //     NotificationManager::getInstance()->unsubscribeTopic(self::$topic1, self::$token1);
    //     NotificationManager::getInstance()->unsubscribeTopic(self::$topic2, self::$token1);
    //     NotificationManager::getInstance()->unsubscribeTopic(self::$topic3, self::$token1);

    //     $subscriptions = NotificationManager::getInstance()->getTopicsSusbcribed(self::$token1);

    //     $this->assertEquals(0, count($subscriptions));
    // }

    // funcion no funcional
    // public function testValidToken(){
    //     $token = 'd9VIxftAB14:APA91bEPKajfIb7--LpXSNOhxwL52D3ib9efWVE7rNP-XRmlrTt0p-7zCw95yPwQ0nxfKx14Yim7gUUqq2w5ctZ0szrHIQBLBrbSm5IeXzlL_84XFCr6uEnxHJUwJz5Zh6sRwRkWRwQd';
    //     NotificationManager::getInstance()->validToken($token);

    //     $this->assertEquals(0, 0);
    // }

    // subscribimos a un array de topicos
    // public function testSubscribeAllTopics(){
    //     $topics = ['topic1', 'topic2', 'topic3'];
    //     $token = 'd9VIxftAB14:APA91bEPKajfIb7--LpXSNOhxwL52D3ib9efWVE7rNP-XRmlrTt0p-7zCw95yPwQ0nxfKx14Yim7gUUqq2w5ctZ0szrHIQBLBrbSm5IeXzlL_84XFCr6uEnxHJUwJz5Zh6sRwRkWRwQd';

    //     NotificationManager::getInstance()->susbcribeAllTopic($token, $topics);

    //     $subscriptions = NotificationManager::getInstance()->getTopicsSusbcribed($token);

    //     $this->assertEquals(2, count($subscriptions));
    // }

    // desusbcribe a un token de sus subcripciones
    // public function testUnsubscribeAllTopics(){
    //     $token = 'd9VIxftAB14:APA91bEPKajfIb7--LpXSNOhxwL52D3ib9efWVE7rNP-XRmlrTt0p-7zCw95yPwQ0nxfKx14Yim7gUUqq2w5ctZ0szrHIQBLBrbSm5IeXzlL_84XFCr6uEnxHJUwJz5Zh6sRwRkWRwQd';

    //     $subscriptionsOld = NotificationManager::getInstance()->getTopicsSusbcribed($token);
    //     $this->assertEquals(2, count($subscriptionsOld));

    //     NotificationManager::getInstance()->unsusbcribeAllTopic($token);

    //     $subscriptionsAfter = NotificationManager::getInstance()->getTopicsSusbcribed($token);

    //     $this->assertEquals(0, count($subscriptionsAfter));
    // }

    // recupera los topicos a los que el usaurio esta subscrito
    // public function testGetSubscribeTopics(){
    //     $token = 'cEYWz4rA8BQ:APA91bFPS3Kq6BrFXG28Ht_5IK4Oa3c1sAEXmmo6DflPlApRz54aST3MYcpCbPiBT0COnrbNX_yv9tu2PsyMR411gvebIQJnhdIB41plN_XpQdhkYpYwm7mk0iNkay3RaqsY1b7-vKdh';
    //     $subscriptions = NotificationManager::getInstance()->getTopicsSusbcribed($token);

    //     foreach ($subscriptions as $subscription) {
    //         // echo "{$subscription->registrationToken()} fue subscrito a {$subscription->topic()}\n";
    //         echo "Esta subscrito a {$subscription->topic()}\n";
    //     }

    //     $this->assertEquals(1, count($subscriptions));
    // }


    // probando el formato de hora
    public function testFormatDateTimezone(){
        $fecha = new DateTime('2000-01-01');
        echo $fecha->format('Y-m-d H:i:sP') . "\n";

        $fecha->setTimezone(new DateTimeZone('Pacific/Chatham'));
        echo $fecha->format('Y-m-d H:i:sP') . "\n";

        $fecha->setTimezone(new DateTimeZone('America/Argentina/Buenos_Aires'));
        echo $fecha->format('Y-m-d H:i:sP') . "\n";

        $this->assertEquals(1, 1);
    }

}