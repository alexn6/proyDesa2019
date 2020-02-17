<?php

use PHPUnit\Framework\TestCase;

use App\Utils\DbClodFirestoreManager;

use Google\Cloud\Core\Timestamp;

class ManagerDbCloudFirestoreTest extends TestCase{

    public function testSizeCollection(){
        // $pathColleccion = 'news-test/comp-test/news';
        $pathColleccion = 'dbproyectotorneos/Olimpico/news';
        
        $size = DbClodFirestoreManager::getInstance()->sizeCollection($pathColleccion);

        $this->assertEquals(1, $size);
    }

    // // recuperamos los datos de un noticia de una coleccion determinada
    // public function testDataDocument(){
    //     $pathColleccion = 'news-test/comp-test/news';
    //     $documentId = '2';
    //     $data = DbClodFirestoreManager::getInstance()->getDataDocumentCollection($pathColleccion, $documentId);
        
    //     var_dump($data);

    //     $this->assertEquals(0, 0);
    // }

    // public function testAddDocument(){
    //     $pathCollection = 'news-test/comp-test/news';
    //     $documentId = 3;
    //     $data = ['title' => 'Inicio de jornada', 
    //                 'resume' => 'Comienza la jornada nº n de la competencia', 
    //                 'descripcion' => 'Aca va toda la info de la noticia',
    //                 'uptime' => new Timestamp(new DateTime())
    //             ];

    //     // recuperamos la cantidad de elementos antes de realizar el insert
    //     $sizeBefore = DbClodFirestoreManager::getInstance()->sizeCollection($pathCollection);

    //     DbClodFirestoreManager::getInstance()->insertDocument($pathCollection, $data, $documentId);

    //     $sizeAfter = DbClodFirestoreManager::getInstance()->sizeCollection($pathCollection);

    //     $this->assertEquals($sizeBefore + 1, $sizeAfter);
    // }

    // public function testGetCollecctionOrderAsc(){
    //     $pathCollection = 'news-test/comp-test/news';
    //     $field = 'uptime';
    //     $collection = DbClodFirestoreManager::getInstance()->getCollectionOrderAsc($pathCollection, $field);

    //     foreach($collection as $document){
    //         echo($document->id()."\n");
    //     }

    //     $this->assertEquals(2, 2);
    // }

    // public function testGetOldestDocument(){
    //     //$pathCollection = 'news-test/comp-test/news';
    //     //$pathCollection = 'dbproyectotorneos/Torneo de Prueba/news';
    //     $fieldTime = 'uptime';
    //     $docSnapshot = DbClodFirestoreManager::getInstance()->getOldestDocument($pathCollection, $fieldTime);

    //     $data = $docSnapshot->data();
        
    //     //var_dump($docSnapshot->id());
    //     // var_dump($data);
    //     // var_dump($docSnapshot->path());
    //     $pathCollection =$docSnapshot->reference()->parent()->path();
    //     $subPath = explode("/", $pathCollection);
    //     var_dump($subPath[1]);

    //     $this->assertEquals(2, 2);
    // }

    // public function testGetNewestNDocuments(){
    //     //$pathCollection = 'news-test/comp-test/news';
    //     $pathCollection = 'dbproyectotorneos/Torneo de Prueba/news';
    //     $fieldTime = 'uptime';
    //     $n = 4;
    //     $arrayDocSnapshot = DbClodFirestoreManager::getInstance()->getLastednDocument($pathCollection, $fieldTime, $n);

    //     foreach($arrayDocSnapshot as $document){
    //         echo(" Id de la noticia => ".$document->id()."\n");
    //     }

    //     $this->assertEquals(4, count($arrayDocSnapshot));
    // }
    

}