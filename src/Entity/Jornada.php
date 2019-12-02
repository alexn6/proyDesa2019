<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Competencia;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JornadaRepository")
 */
class Jornada
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $numero;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fecha;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Competencia")
     * @ORM\JoinColumn(name="competencia_id", referencedColumnName="id")
     */
    private $competencia;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $fase;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(?\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getCompetencia(): ?Competencia
    {
        return $this->competencia;
    }

    public function setCompetencia(?Competencia $competencia): self
    {
        $this->competencia = $competencia;

        return $this;
    }

    public function getFase(): ?int
    {
        return $this->fase;
    }

    public function setFase(int $fase): self
    {
        $this->fase = $fase;

        return $this;
    }

    
}