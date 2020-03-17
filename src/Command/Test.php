<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Entity\Inscripcion;

use Doctrine\ORM\EntityManagerInterface;

class Test extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:test';
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->em->getRepository(Inscripcion::class);
        
        $insccripciones = $repository->findall();
        
        $output->writeln('Pasa la creacion del em -> test.');
        $output->writeln('Cant de inscripciones.  -> test: '.count($insccripciones));

        return 0;
    }
}