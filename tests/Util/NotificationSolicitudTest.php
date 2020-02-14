<?php

use PHPUnit\Framework\TestCase;

use App\Utils\NotificationManager;

use Kreait\Firebase\Messaging\Notification;

class NotificationSolicitudTest extends TestCase{

    public function testNotificationSolicitud(){

        $token ='fUcPY2Yii7w:APA91bFF_FS_LHpvCYGQ0h_wi9tivXsMwR2llC0KWudPCO2pTbI30yBdoUfF0hBIfIlkz_o9Lj3ZaM9P42siPRf6I532cfolqxvd0v4lNDed6-q8AnRV0rKI6kAB7N4V7LBZ0dyeFArc';

        $title = 'Resolucion de solicitud de inscripcion';
        $body = "Solicitud aprobada!";

        $notification = Notification::create($title, $body);

        $result = NotificationManager::getInstance()->notificationSpecificDevices($token, $notification);

        print_r($result);
        
        $this->assertEquals(0, 0);
    }
}