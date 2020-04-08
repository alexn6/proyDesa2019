<?php

// namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Utils\NotificationManager;

use Kreait\Firebase\Messaging\Notification;
use App\Utils\Constant;

class NotificationTopicCompetitionTest extends TestCase{

    public static $tokenSeg = 'du2Cn5Ku5ls:APA91bFMm6Bf9-hG7GYs--4pNokqJoTe0ZZZvMsB681z381P3D5IIZSh6VZ2zrqClvxvk2bQZWEAkFCXApj2foVSgLN-i6mnDR7GYJtQdxMUqQTsGEejSEWs-9A7faBUIoOHBptkS-0l';
    public static $nombreComp = "TorneodePrueba";

    // tener en cuenta la demora del registro de subscripcion
    // tal vez con una promesa o algo asi se podria controlar esto

    // recuperamos todos los topicos a los que se subscribe un token (instancia)
    // public function testNotifTopicCompetition(){

    //     $topicFollowers = self::$nombreComp. '-' .Constant::ROL_SEGUIDOR;
    //     //$topicCompetitors = self::$nombreComp. '-' .Constant::ROL_COMPETIDOR;

    //     echo($topicFollowers);

    //     $title = 'Competencia: '.self::$nombreComp;
    //     $body = 'La competencia paso a FINALIZADA';

    //     $notification = Notification::create($title, $body);

    //     NotificationManager::getInstance()->notificationToTopic($topicFollowers, $notification);
    //     //NotificationManager::getInstance()->notificationToTopic($topicCompetitors, $notification);

    //     $this->assertEquals(0, 0);
    // }

    // public function testGetTopics(){
    //     //$topicFollowers = self::$nombreComp. '-' .Constant::ROL_SEGUIDOR;
    //     //NotificationManager::getInstance()->subscribeTopic($topicFollowers, self::$tokenSeg);
    //     $tokenCompetidor = 'edNtmgk1bHM:APA91bHmrsYr6_A4MdiMEt93kCRPb6oFjWqTjyetnF3Zua32doxc6I306aTqMhihRFgfbFzrYssRzAQLJcFx827y8SONfAdVHPS2pAnhvkEmNDlI5mmDnvztiEBvaUEnAcXZr6KdcqYB';
    //     $subscriptions = NotificationManager::getInstance()->getTopicsSusbcribed($tokenCompetidor);

    //     foreach ($subscriptions as $subscription) {
    //         echo "Esta subscripto a {$subscription->topic()}\n";
    //     }

    //     $this->assertEquals(0, 0);
    // }

    public function testTopicsCompetitors(){
        $topicCompetitors = "GrupSigFase2". '-' .Constant::ROL_COMPETIDOR;
        
        $notification = Notification::create("Notif competidor", "Test de notifi p/competidores.");

        NotificationManager::getInstance()->notificationToTopic($topicCompetitors, $notification);
    }


}