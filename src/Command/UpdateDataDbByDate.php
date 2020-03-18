<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use Doctrine\ORM\EntityManagerInterface;

use \Datetime;

use App\Entity\Inscripcion;
use App\Entity\Competencia;
use App\Utils\ControlDate;
use App\Utils\Constant;

use Kreait\Firebase\Messaging\Notification;
use App\Utils\NotificationManager;

class UpdateDataDbByDate extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:updatadb';
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        // $this->requirePassword = $requirePassword;

        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->updateInscriptions($output);

        return 0;
    }

    // actualiza el estado de las competencias en base a su inscripcion
    private function updateInscriptions(OutputInterface $output){
        $repository = $this->em->getRepository(Inscripcion::class);

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        
        $inscripciones = $repository->findall();

        $inscripciones = $serializer->serialize($inscripciones, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            },
            'ignored_attributes' => ['competencia']
        ]);

        // pasamos los datos a un array para poder trabajarlos
        $inscripciones = json_decode($inscripciones, true);

        $fechaActual = new DateTime();
        // pasamos el dato de la a un formato mas simple
        for ($i=0; $i < count($inscripciones); $i++){
            $this->controlInitInscription($inscripciones[$i]);
        }

        $output->writeln('Cant de inscripciones: '.count($inscripciones));
    }


    // ##################################################################
    // #################### funciones auxiliares ####################

    // controla si es el dia de apertura de la inscripcion
    private function controlInitInscription($dataInscripction){
        $timestamp = $dataInscripction['fechaIni']['timestamp'];
        $fechaInicio = new DateTime();
        $fechaInicio->setTimestamp($timestamp);

        $isTodayOpen = ControlDate::getInstance()->isToday($fechaInicio);
        // IMPORTANTE: cuando es false no se imprime valor en la pantalla de la consola
        if($isTodayOpen){
            $idInscription = $dataInscripction['id'];
            $repository = $this->em->getRepository(Inscripcion::class);
            $inscription = $repository->find($idInscription);
            // buscamos la competencia para actualizar su estado
            $repositoryComp = $this->em->getRepository(Competencia::class);
            $competencia = $repositoryComp->findOneBy(['inscripcion' => $inscription]);
            // actualizamos el estado de la competencia
            $this->changeStatusCompetition($competencia, Constant::ESTADO_COMP_INSCRIPCION_ABIERTA);
            // mandamos la notificacion al topico
            $this->sendNotificationInitInscription($competencia);
        }
    }

    // cambia el esatdo de la competencia
    private function changeStatusCompetition($competencia, $newStatus){
        $competencia->setEstado($newStatus);

        $this->em->flush();        
    }

    // Mandamos la notificacion del cambio de estado de la competencia
    private function sendNotificationInitInscription($competencia){
        $resumenNoticia = "La inscripcion de la competencia esta abierta.";
        $nameComp = str_replace(' ', '', $competencia->getNombre());

        $topicFollowers = $nameComp. '-' .Constant::ROL_SEGUIDOR;
        $topicCompetitors = $nameComp. '-' .Constant::ROL_COMPETIDOR;

        $title = $competencia->getNombre();
        
        $notification = Notification::create($title, $resumenNoticia);

        NotificationManager::getInstance()->notificationToTopic($topicFollowers, $notification);
        NotificationManager::getInstance()->notificationToTopic($topicCompetitors, $notification);
    }
}