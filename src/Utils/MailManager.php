<?php

namespace App\Utils;

use App\Utils\Constant;
use App\Utils\ParamsProject;


// administrador de mail - SINGLETON
class MailManager
{
    private static $instance;
    private static $manager;

    protected static $swfMailerServer;
    protected static $swfMailerServerPort;
    protected static $swfMailerServerSecure;
    protected static $swfMailerServerAccount;
    protected static $swfMailerServerAccountPass;

    private function __construct()
    {
        $this->loadParamServer();

        $transport = (new \Swift_SmtpTransport(
                                self::$swfMailerServer,
                                self::$swfMailerServerPort, 
                                self::$swfMailerServerSecure))
            ->setUsername(self::$swfMailerServerAccount)
            ->setPassword(self::$swfMailerServerAccountPass)
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


    // Envia un correo con la cuenta del servidor smtp configurado
    public function sendMail($issue, $to, $body){
        $message = self::createMsg($issue, $to, $body);

        self::$manager->send($message);
    }



    // Creamos el mje con los parametros recibidos, con la cuenta del servidor smtp configurado
    private function createMsg($issue, $to, $bodyMsg){
        $message = (new \Swift_Message($issue))
            ->setFrom(self::$swfMailerServerAccount)
            ->setTo($to)
            ->setBody($bodyMsg)
        ;

        return $message;
    }

    // Envia un correo
    public function sendMailPublic($issue, $from, $to, $body){
        $message = self::createMsg($issue, $from, $to, $body);

        self::$manager->send($message);
    }



    // Creamos el mje con los parametros recibidos
    private function createMsgPublic($issue, $from, $to, $bodyMsg){
        $message = (new \Swift_Message($issue))
            ->setFrom($from)
            ->setTo($to)
            ->setBody($bodyMsg)
        ;

        return $message;
    }

    // recuperamos los valoes de configuracion del servidor
    private function loadParamServer(){
        // recuperamos los datos de configuracion del servidor
        $adminPath = new ParamsProject();
        $pathRoot = $adminPath->getRoot();
        $source= file_get_contents($pathRoot.'/config/serverMail.json'); 
        $params = json_decode($source, true);

        self::$swfMailerServer = $params["SERVER_SMTP"];
        self::$swfMailerServerPort = $params["SERVER_SMTP_PORT"];
        self::$swfMailerServerSecure = $params["SERVER_SMTP_SECURE"];
        self::$swfMailerServerAccount = $params["SERVER_SMTP_ACCOUNT"];
        self::$swfMailerServerAccountPass = $params["SERVER_SMTP_ACCOUNT_PASS"];
    }

    public static function getAccountServer(){
        return self::$swfMailerServerAccount;
    }

}