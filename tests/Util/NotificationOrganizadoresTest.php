<?php

use PHPUnit\Framework\TestCase;

use App\Utils\NotificationManager;

use Kreait\Firebase\Messaging\Notification;

class NotificationOrganizadoresTest extends TestCase{

    // mandamos una notificacion de prueba a varios dispositivos
    // public function testNotificationOrganizadores(){

    //     $tokenOrg1 ='fUcPY2Yii7w:APA91bFF_FS_LHpvCYGQ0h_wi9tivXsMwR2llC0KWudPCO2pTbI30yBdoUfF0hBIfIlkz_o9Lj3ZaM9P42siPRf6I532cfolqxvd0v4lNDed6-q8AnRV0rKI6kAB7N4V7LBZ0dyeFArc';
    //     $tokenOrg2 = 'eOCyHO6FkxE:APA91bH6y-nDnsRMW4Xo-4sdN44g_Z9Y89ai0yNX-RU-3TpVNi5fXwF0vQybMlwkitHwcHXCwwK1ZoMVa4AwgOGCVMWQm0j_sTCpjMFHxaV-0oTjfk1fWBb2761nZivDBP_pPmWyV8wl';
    //     $tokenTest = 'edNtmgk1bHM:APA91bHmrsYr6_A4MdiMEt93kCRPb6oFjWqTjyetnF3Zua32doxc6I306aTqMhihRFgfbFzrYssRzAQLJcFx827y8SONfAdVHPS2pAnhvkEmNDlI5mmDnvztiEBvaUEnAcXZr6KdcqYB';
    //     // preparamos el array de tokens
    //     // $tokenDevices = [$tokenOrg1, $tokenOrg2];
    //     $tokenDevices = [$tokenTest];
    //     var_dump($tokenDevices);

    //     $title = "Solicitud de inscripcion";
    //     $body = "El usuario SeñorX quiere unirse a la competencia";
    //     $notification = Notification::create($title, $body);

    //     $result = NotificationManager::getInstance()->notificationMultipleDevices($tokenDevices, $notification);

    //     print_r($result);
        
    //     $this->assertEquals(0, 0);
    // }

    // mandamos una notificacion de prueba con datos a varios dispositivos
    public function testNotificationMultipleDevicesWithData(){

        $data = [
            'COMPETENCIA_ID' => '23'
        ];

        $tokenTest = 'edNtmgk1bHM:APA91bHmrsYr6_A4MdiMEt93kCRPb6oFjWqTjyetnF3Zua32doxc6I306aTqMhihRFgfbFzrYssRzAQLJcFx827y8SONfAdVHPS2pAnhvkEmNDlI5mmDnvztiEBvaUEnAcXZr6KdcqYB';
        // preparamos el array de tokens
        $tokenDevices = [$tokenTest];
        var_dump($tokenDevices);

        $title = "Solicitud de inscripcion";
        $body = "El usuario SeñorX quiere unirse a la competencia";
        $notification = Notification::create($title, $body);

        $result = NotificationManager::getInstance()->notificationMultipleDevicesWithData($tokenDevices, $notification, $data);

        print_r($result);

        $this->assertEquals(0, 0);
    }
}