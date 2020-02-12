<?php

use PHPUnit\Framework\TestCase;
//use Kreait\Firebase;
use App\Utils\DbClodFirestoreManager;

// use Kreait\Firebase\Messaging\Notification;
// use App\Utils\Constant;

// use Google\Cloud\Firestore\FirestoreClient;
// require '/var/www/html/proyDesa2019/vendor/autoload.php';

class ManagerDbCloudFirestoreTest extends TestCase{

    // public function testCreateDbFirestore(){
    //     $firestore = new FirestoreClient([
    //         'projectId' => 'proyectotorneosfcm'
    //     ]
    //     );

    //     // creamos una db y agregamos algunas noticias de prueba
    //     $collectionReference = $firestore->collection('news-test');

    //     $new1 = $collectionReference->add([
    //         'new_one' => 'Noticia de prueba 1'
    //     ]);
    //     $new2 = $collectionReference->add([
    //         'new_two' => 'Noticia de prueba 2'
    //     ]);

    //     // recuperamos todas las noticias de la db
    //     $documents = $collectionReference->listDocuments();
    //     foreach ($documents as $document) {
    //         echo $document->name() . PHP_EOL;
    //     }


    //     // $factory = new Firebase\Factory();
    //     // $firestore = $factory->createFirestore();

    //     // $database = $firestore->database();

    //     // $new1 = $database->add([
    //     //     'new_one' => 'Noticia de prueba 1'
    //     // ]);
    //     // $new2 = $database->add([
    //     //     'new_two' => 'Noticia de prueba 2'
    //     // ]);

    //     // // recuperamos todas las noticias de la db
    //     // $documents = $database->listDocuments();
    //     // foreach ($documents as $document) {
    //     //     echo $document->name() . PHP_EOL;
    //     // }

    //     $this->assertEquals(0, 0);
    // }

    // // recuperamos una noticia especifica de una competencia por medio de su id y accedemos a sus datos
    // public function testGetDataDbCloudOneNews(){
    //     $firestore = new FirestoreClient([
    //         'projectId' => 'proyectotorneosfcm'
    //     ]
    //     );

    //     $document = $firestore->document('news-test/comp-test/news/1');
    //     $snapshot = $document->snapshot();
    //     $infoNoticia = $snapshot->data();

    //     //var_dump($document);
    //     var_dump($document->id());
    //     //var_dump($snapshot);
    //     var_dump($infoNoticia);

    //     $this->assertEquals(0, 0);
    // }

    // // recuperamos un documento especifico por medio de su id y accedemos a sus datos
    // public function testGetDataDbCloudAllNews(){
    //     $firestore = new FirestoreClient([
    //         'projectId' => 'proyectotorneosfcm'
    //     ]
    //     );

    //     $collectionNewsCompetition = $firestore->collection('news-test/comp-test/news');    

    //     // $new = $collectionNewsCompetition->document('1');
    //     // $snapshotNew = $new->snapshot();
    //     // $infoNew = $snapshotNew->data();


    //     // vemos la cantidad de noticias de la competencia
    //     $newsCompetition = $collectionNewsCompetition->listDocuments();
    //     foreach ($newsCompetition as $document) {
    //         $snapshot = $document->snapshot();
    //         $infoNoticia = $snapshot->data();
    //         echo("Titulo de la noticia: ".$infoNoticia['title']."\n");
    //     }

    //     // coomprobamos la cant de noticias de la competencia
    //     $this->assertEquals(2, iterator_count($newsCompetition));
    // }

        public function testSizeCollection(){
            $pathColleccion = 'news-test/comp-test/news';
            $size = DbClodFirestoreManager::getInstance()->sizeCollection($pathColleccion);

            $this->assertEquals(2, $size);
        }

        public function testDataDocument(){
            $pathColleccion = 'news-test/comp-test/news';
            $documentId = '2';
            $data = DbClodFirestoreManager::getInstance()->getDataDocumentCollection($pathColleccion, $documentId);
            
            var_dump($data);

            $this->assertEquals(0, 0);
        }

}