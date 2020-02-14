<?php

use PHPUnit\Framework\TestCase;

use App\Utils\NotificationManager;

use Kreait\Firebase\Messaging\Notification;

class NotificationOrganizadoresTest extends TestCase{

    public function testNotificationOrganizadores(){

        $tokenOrg1 ='fUcPY2Yii7w:APA91bFF_FS_LHpvCYGQ0h_wi9tivXsMwR2llC0KWudPCO2pTbI30yBdoUfF0hBIfIlkz_o9Lj3ZaM9P42siPRf6I532cfolqxvd0v4lNDed6-q8AnRV0rKI6kAB7N4V7LBZ0dyeFArc';
        $tokenOrg2 = 'eOCyHO6FkxE:APA91bH6y-nDnsRMW4Xo-4sdN44g_Z9Y89ai0yNX-RU-3TpVNi5fXwF0vQybMlwkitHwcHXCwwK1ZoMVa4AwgOGCVMWQm0j_sTCpjMFHxaV-0oTjfk1fWBb2761nZivDBP_pPmWyV8wl';
        // preparamos el array de tokens
        $tokenDevices = [$tokenOrg1, $tokenOrg2];
        var_dump($tokenDevices);

        $title = "Solicitud de inscripcion";
        $body = "El usuario SeñorX quiere unirse a la competencia";
        $notification = Notification::create($title, $body);

        $result = NotificationManager::getInstance()->notificationMultipleDevices($tokenDevices, $notification);

        print_r($result);
        
        $this->assertEquals(0, 0);
    }
}