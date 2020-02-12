<?php

namespace App\Utils;

use App\Utils\Constant;

require '/var/www/html/proyDesa2019/vendor/autoload.php';

use Google\Cloud\Firestore\FirestoreClient;

// administrador de la db cloudFirestore - SINGLETON
class DbClodFirestoreManager
{
    private static $instance;
    private static $manager;

    private function __construct()
    {
        // creamos el admin con mlos permisos y cuenta de servicio especificados
        self::$manager = new FirestoreClient([
            'projectId' => Constant::PROJECT_FIREBASE
        ]
        );
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    // Envia un mensaje al token recibido
    public function getCollection($pathCollection){
        $collection;
        try {
            // recuperamos la coleccion de documentos
            $collection = self::$manager->collection($pathCollection);
        } catch (InvalidMessage $e) {
            print_r($e->errors());

            return;
        }
    
        return $collection->listDocuments();;
    }

    // Envia un mensaje al token recibido
    public function sizeCollection($pathCollection){
        $collection;
        try {
            // recuperamos la coleccion de documentos
            $collection = self::$manager->collection($pathCollection);
        } catch (InvalidMessage $e) {
            print_r($e->errors());

            return;
        }
        // vemos la cantidad de documentos
        $ndocuments = $collection->listDocuments();
    
        return iterator_count($ndocuments);
    }

    // recuperamos la info del documento de la collecion seleccionada
    // NOTA: controlar q exista la collecion y el documento
    public function getDataDocumentCollection($pathCollection, $documentId){
        
        $collection;
        try {
            // recuperamos la coleccion de documentos
            $collection = self::$manager->collection($pathCollection);
        } catch (InvalidMessage $e) {
            print_r($e->errors());
            return;
        }
        $document = $collection->document($documentId);
        $snapshot = $document->snapshot();
        
        return $snapshot->data();
    }

}