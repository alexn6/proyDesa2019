<?php

// namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Utils\NotificationManager;

use Kreait\Firebase\Messaging\Notification;
use App\Utils\Constant;

class NotificationTopicCompetitionTest extends TestCase{

    public static $tokenSeg = 'dYVgTrDaIC8:APA91bEQ86LACFK2NrDBmUsbF62xs36gHeJWY017xXx-5iBOTxNdYWySXJYiEonkgU_CHAP_pdsMF6Tyoai8JaYnFnaZ5lX-73fykLkrVUUM7fYU99YdtjDVuqhuEI9IQ5XEqs9N6PWG';
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

    public function testGetTopics(){
        //$topicFollowers = self::$nombreComp. '-' .Constant::ROL_SEGUIDOR;
        //NotificationManager::getInstance()->subscribeTopic($topicFollowers, self::$tokenSeg);

        $subscriptions = NotificationManager::getInstance()->getTopicsSusbcribed(self::$tokenSeg);

        foreach ($subscriptions as $subscription) {
            echo "Esta subscripto a {$subscription->topic()}\n";
        }

        $this->assertEquals(0, 0);
    }

}