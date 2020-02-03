<?php

namespace App\Utils;

use App\Utils\Constant;


// administrador de mail - SINGLETON
class MailManager
{
    private static $instance;
    private static $manager;

    private function __construct()
    {
        echo 'Construyendo el admin de mail..'.PHP_EOL;

        $transport = (new \Swift_SmtpTransport(Constant::SWFMAILER_SERVER_SMTP,
                                        Constant::SWFMAILER_SERVER_SMTP_PORT, 
                                        Constant::SWFMAILER_SERVER_SMTP_SECURE))
            ->setUsername(Constant::SWFMAILER_SERVER_SMTP_USER)
            ->setPassword(Constant::SWFMAILER_SERVER_SMTP_USER_PASS)
            ;

        // creamos el admin de mails
        self::$manager = new \Swift_Mailer($transport);
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }



    // Envia un correo
    public function sendMail($issue, $from, $to, $body){
        $message = self::createMsg($issue, $from, $to, $body);

        self::$manager->send($message);
    }



    // Creamos el mje con los parametros recibidos
    private function createMsg($issue, $from, $to, $bodyMsg){
        $message = (new \Swift_Message($issue))
            ->setFrom($from)
            ->setTo($to)
            ->setBody($bodyMsg)
        ;

        return $message;
    }

}