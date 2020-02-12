<?php

use PHPUnit\Framework\TestCase;

use App\Utils\DbClodFirestoreManager;

use Google\Cloud\Core\Timestamp;

class ManagerDbCloudFirestoreTest extends TestCase{

    // public function testSizeCollection(){
    //     $pathColleccion = 'news-test/comp-test/news';
    //     $size = DbClodFirestoreManager::getInstance()->sizeCollection($pathColleccion);

    //     $this->assertEquals(2, $size);
    // }

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

    public function testGetCollecctionOrderAsc(){
        $pathCollection = 'news-test/comp-test/news';
        $field = 'uptime';
        $collection = DbClodFirestoreManager::getInstance()->getCollectionOrderAsc($pathCollection, $field);
        //$collection = DbClodFirestoreManager::getInstance()->getCollection($pathCollection);

        foreach($collection as $document){
            echo($document->id()."\n");
        }

        $this->assertEquals(2, 2);
    }

}