<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ResultadoRepository")
 */
class Resultado
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UsuarioCompetencia")
     * @ORM\JoinColumn(name="competidor_id", referencedColumnName="id")
     */
    private $competidor;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $jugados;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ganados;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $empatados;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $perdidos;
    
}