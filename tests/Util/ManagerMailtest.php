<?php

use PHPUnit\Framework\TestCase;

class ManagerMailtest extends TestCase{

    // puerto smtp.gmail tls => 587
    // puerto smtp.gamil ssl => 465

    public function testSendMail(){
        
        $transport = (new \Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
            ->setUsername('alex6tc90@gmail.com')
            ->setPassword('G1m2a3i4l')
            ;

        // Create the Mailer using your created Transport
        $mailer = new \Swift_Mailer($transport);

        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('alex6tc90@gmail.com')
            ->setTo('sergio19101992@gmail.com')
            ->setBody('You should see me from the profiler!')
        ;

        $mailer->send($message);

        $this->assertEquals(0, 0);
    }

}