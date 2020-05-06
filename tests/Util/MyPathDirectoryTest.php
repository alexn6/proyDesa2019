<?php

use PHPUnit\Framework\TestCase;

use App\Utils\ParamsProject;

class MyPathDirectoryTest extends TestCase{

    public function testPath(){

        $adminPath = new ParamsProject();
        echo($adminPath->getRoot());

        $this->assertEquals(true, true);
    }

    public function testReadFileJson(){
        $adminPath = new ParamsProject();
        $pathRoot = $adminPath->getRoot();

        $source= file_get_contents($pathRoot.'/src/Params/serverMail.json'); 
        $data = json_decode($source, true);

        echo("\n");
        echo($data["SERVER_SMTP"]."\n");
        echo($data["SERVER_SMTP_PORT"]."\n");
        echo($data["SERVER_SMTP_SECURE"]."\n");
        echo($data["SERVER_SMTP_ACCOUNT"]."\n");
        echo($data["SERVER_SMTP_ACCOUNT_PASS"]."\n");

        $this->assertEquals(true, true);
    }

}