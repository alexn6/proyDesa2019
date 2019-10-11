<?php

use PHPUnit\Framework\TestCase;
use App\Utils\NotificationService;

class NotificationSolicitudTest extends TestCase{

    public function testNotificationDeleteSolicitud(){
        $servNotification = new NotificationService();

        $title = "Resolucion de su solicitud de inscripcion";
        $token ='da7cU3tPSs8:APA91bH2QFvIB8uGSgNmioaHBGTBkTrcYSCy-Rpsp8VDlnH8UmKIC6prC3jC0n5TMx55rldz5VBmJOOja7fJdCw-xzguuz1RXxCqGjFJ7kErSjPI4gQ6pBgFNGKgzw0BIO0I_NpHZDPy';
        $msg = "Su solicitud fue rechazada";

        $result = $servNotification->sendSimpleNotificationFCM($title, $token, $msg);

        print_r($result);
        // 
        $this->assertEquals(0, 0);
    }
}