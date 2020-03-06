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
    * @ORM\Column(type="time", nullable=false)
    */
    private $hora_desde;

    /**
    * @ORM\Column(type="time", nullable=false)
    */
    private $hora_hasta;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getHoraDesde(): ?\DateTimeInterface
    {
        return $this->hora_desde;
    }

    public function setHoraDesde(\DateTimeInterface $hora_desde): self
    {
        $this->hora_desde = $hora_desde;

        return $this;
    }

    public function getHoraHasta(): ?\DateTimeInterface
    {
        return $this->hora_hasta;
    }

    public function setHoraHasta(\DateTimeInterface $hora_hasta): self
    {
        $this->hora_hasta = $hora_hasta;

        return $this;
    }
}
