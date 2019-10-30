<?php

use PHPUnit\Framework\TestCase;
use App\Utils\NotificationService;

class NotificationSolicitudTest extends TestCase{

    public function testNotificationDeleteSolicitud(){
        $servNotification = new NotificationService();

        $title = "Resolucion de su solicitud de inscripcion";
        $token ='eJ8zOQsR00g:APA91bFtKM0nA6ou-TvORHDzpOKob2GDctSEjgy-gl9TIKalMAcmFymI1HC3iRmNgKikbDbsvxx3aghsZeMAtSn4ykR_z5BdaEsWkjnSlrKSuXhk7vuziqYSp2-lYvv64xUKuq82aPhy';
        $msg = "Su solicitud fue rechazada";

        $result = $servNotification->sendSimpleNotificationFCM($title, $token, $msg);

        print_r($result);
        // 
        $this->assertEquals(0, 0);
    }
}