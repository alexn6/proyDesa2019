<?php

use PHPUnit\Framework\TestCase;

use App\Utils\VerificationMail;

class VerificationMailTest extends TestCase{


    public function testCorrect(){

        $email = 'alex6tc90@gmail.com';

        $verificador = new VerificationMail();

        $isEmail = $verificador->verify($email);

        $this->assertEquals($isEmail, true);
    }

    public function testIncorrect(){

        $email = 'alex6fgf_dtc90@gmail.com';

        $verificador = new VerificationMail();

        $isEmail = $verificador->verify($email);

        $this->assertEquals(false, $isEmail);
    }

}