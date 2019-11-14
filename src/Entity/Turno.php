<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Competencia;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TurnoRepository")
 */

 class Turno{
               
    /**
    * @ORM\Id
    * @ORM\GeneratedValue
    * @ORM\Column(type="integer")
    */
    private $id;

    /**
    * @ORM\ManyToOne(targetEntity="App\Entity\Competencia")
    * @ORM\JoinColumn(name="competencia_id", referencedColumnName="id")
    */
    private $competencia;

    /**
    * @ORM\Column(type="date", nullable=false)
    */
    private $hora;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHora(): ?\DateTimeInterface
    {
        return $this->hora;
    }

    public function setHora(\DateTimeInterface $hora): self
    {
        $this->hora = $hora;

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
}
