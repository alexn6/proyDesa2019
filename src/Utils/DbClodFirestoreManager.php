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


    // Recuperamos los elementos de una coleccion
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

        //var_dump($snapshot);
        
        return $snapshot->data();
    }

    public function insertDocument($pathCollection, $data, $documentId){
        $collection;
        try {
            // recuperamos la coleccion de documentos
            $collection = self::$manager->collection($pathCollection);
        } catch (InvalidMessage $e) {
            print_r($e->errors());
            return;
        }
        $collection->document($documentId)->set($data);
    }

    // Recuperamos los elementos de una coleccion ordenados por el campo especificado
    // de manera ascendente
    public function getCollectionOrderAsc($pathCollection, $field){
        $collection;
        try {
            // recuperamos la coleccion de documentos
            $collection = self::$manager->collection($pathCollection);
        } catch (InvalidMessage $e) {
            print_r($e->errors());

            return;
        }

        $query = $collection->orderBy($field, 'ASC');
        $snapshot = $query->documents();
    
        return $snapshot;
    }
}