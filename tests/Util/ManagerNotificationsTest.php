<?php

// namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Utils\NotificationManager;

use Kreait\Firebase\Messaging\Notification;

class ManagerNotificationsTest extends TestCase{

    public static $miToken = 'dx3sViEPObw:APA91bHxTYWG4RSpu3Tza86_nVp2vXEnKPhZXzct-70-GoO1VdgT2Jbl2slpnC2NLLytEo7qmcuaH_jrPE6z7Xr5u4kCpoRJ0muKzu_HVThj_tzsTha-l2WmBNBXQ8vnglbmCIbwHhAy';
    public static $tokenKm = 'edvr0L4V6vI:APA91bHYAyHoy_WyP3-eRgCFturudsbrsaaYbj3cksR3wtzLbQmbF_3ga81Ei9_oWwxpNbgg1buAN3-ODFoUyfvUeW2D6u9MGEHiIjQ69fSCSkiWQ_3UlVMkI17cHoJ50LlL_4SokdmO';
    public static $topic = "my-firts-topic";

    // public function testNotificationSpecificDevices(){
    //     $title = 'title spec-dev';
    //     $body = 'body spec-dev';
    //     $notification = Notification::create($title, $body);

    //     NotificationManager::getInstance()->notificationSpecificDevices(self::$miToken, $notification);

    //     $this->assertEquals(0, 0);
    // }

    public function testNotificationSpecificDeviceswithData(){
        $title = 'title spec-dev-data';
        $body = 'body spec-dev-data';
        $notification = Notification::create($title, $body);

        $data = [
            'first_key' => 'First Value',
            'second_key' => 'Second Value',
        ];

        NotificationManager::getInstance()->notificationSpecificDevicesWithData(self::$tokenKm, $notification, $data);

        $this->assertEquals(0, 0);
    }

    // public function testNotificationMultipleDevices(){
    //     $title = 'title multiple';
    //     $body = 'body multiple';

    //     $notification = Notification::create($title, $body);
    //     $deviceTokens = [self::$miToken, self::$tokenKm];

    //     NotificationManager::getInstance()->notificationMultipleDevices($deviceTokens, $notification);

    //     $this->assertEquals(0, 0);
    // }

    // public function testNotificationMultipleDevicesWithData(){
    //     $title = 'title multiple data';
    //     $body = 'body multiple data';

    //     $data = [
    //         'first_key' => 'First Value',
    //         'second_key' => 'Second Value',
    //     ];

    //     $notification = Notification::create($title, $body);
    //     $deviceTokens = [self::$miToken, self::$tokenKm];

    //     NotificationManager::getInstance()->notificationMultipleDevices($deviceTokens, $notification, $data);

    //     $this->assertEquals(0, 0);
    // }

    // public function testNotificationTopic(){
    //     $title = 'title test notif';
    //     $body = 'body test notif';

    //     $notification = Notification::create($title, $body);

    //     NotificationManager::getInstance()->notificationToTopic(self::$topic, $notification);

    //     $this->assertEquals(0, 0);
    // }

    // public function testNotificationTopicWithData(){
    //     $title = 'title test data';
    //     $body = 'body test data';

    //     $notification = Notification::create($title, $body);
    //     $data = [
    //         'first_key' => 'First Value',
    //         'second_key' => 'Second Value',
    //     ];

    //     NotificationManager::getInstance()->notificationToTopicWithData(self::$topic, $notification, $data);

    //     $this->assertEquals(0, 0);
    // }

}