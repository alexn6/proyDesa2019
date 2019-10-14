<?php

use PHPUnit\Framework\TestCase;
use App\Utils\NotificationService;

class NotificationOrganizadoresTest extends TestCase{

    public function testNotificationOrganizadores(){
        $servNotification = new NotificationService();

        $title = "Solicitud de inscripcion";
        $token ='da7cU3tPSs8:APA91bH2QFvIB8uGSgNmioaHBGTBkTrcYSCy-Rpsp8VDlnH8UmKIC6prC3jC0n5TMx55rldz5VBmJOOja7fJdCw-xzguuz1RXxCqGjFJ7kErSjPI4gQ6pBgFNGKgzw0BIO0I_NpHZDPy';

        // preparamos el array de tokens
        $miToken = "f4SIsbWupSs:APA91bEcEIY9hbGVhAIBZOwDt60z325H_f0xyopyOzCzHub0qCMhXwyye9X_7VmF5fJNDjipB5fnF3gnGuW7L8D8jy6PNTM0yVmu7ddKaupmzwJZRCk-At2nPM-Du2u6OZl1qKpeK6Fl";
        // $gorToken = "dXI4g5vjJj4:APA91bE-XP3JP5LL1zGl_5ZRfSzFW57AEfSB8ucScJB1o60Fe4qzEHgYx3KgPI9Yj1qdawkfeSZMQ3K9JKINl-lGfW9jpqIFyqIqtcHmHy6k-X1oq1u6QG1JxZxEYwUNQx4oTLnEcmH1";
        $sergio = "eqze7kZM1qg:APA91bF_9xt695EyqcsUJYQeYY7kEzZw-cF9V-PDl9o_DS2PuL98GlUUW8UyfDZrWjibDhcVd3Qv0MzJoqSMLr23coRNtoByPwfSG0Gmt0nNbXolgGwzfWC1cSi9bSylnSm1H2hO9yNE";
        $tokenDevices = [$miToken, $sergio];

        $msg = "El usuario SeñorX quiere unirse a la competencia";

        $result = $servNotification->sendMultipleNotificationFCM($title, $tokenDevices, $msg);

        print_r($result);
        // 
        $this->assertEquals(0, 0);
    }
}