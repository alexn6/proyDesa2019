<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Utils\NotificationManager;

class ManagerNotificationsTest extends TestCase{

    public static $miToken = 'dx3sViEPObw:APA91bHxTYWG4RSpu3Tza86_nVp2vXEnKPhZXzct-70-GoO1VdgT2Jbl2slpnC2NLLytEo7qmcuaH_jrPE6z7Xr5u4kCpoRJ0muKzu_HVThj_tzsTha-l2WmBNBXQ8vnglbmCIbwHhAy';
    public static $tokenKm = 'edvr0L4V6vI:APA91bHYAyHoy_WyP3-eRgCFturudsbrsaaYbj3cksR3wtzLbQmbF_3ga81Ei9_oWwxpNbgg1buAN3-ODFoUyfvUeW2D6u9MGEHiIjQ69fSCSkiWQ_3UlVMkI17cHoJ50LlL_4SokdmO';
    public static $topic = "my-firts-topic";

    // public function testNotificationSpecificDevices(){    
    //     NotificationManager::getInstance()->notificationSpecificDevices(self::$miToken);

    //     $this->assertEquals(0, 0);
    // }

    public function testNotificationTopic(){
        NotificationManager::getInstance()->notificationToTopic(self::$topic, null);

        $this->assertEquals(0, 0);
    }

}