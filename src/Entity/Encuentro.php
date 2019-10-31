<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Usuario;
use App\Entity\Competencia;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EncuentroRepository")
 */
class Encuentro
{
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Usuario")
     * @ORM\JoinColumn(name="compuser1_id", referencedColumnName="id")
     */
    private $competidor1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Usuario")
     * @ORM\JoinColumn(name="compuser2_id", referencedColumnName="id")
     */
    private $competidor2;

    /**
     * @ORM\Column(type="integer",  nullable=true)
     */
    private $grupo;

    /**
     * @ORM\Column(type="integer")
     */
    private $jornada;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGrupo(): ?int
    {
        return $this->grupo;
    }

    public function setGrupo(int $grupo): self
    {
        $this->grupo = $grupo;

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

    public function getCompetidor1(): ?Usuario
    {
        return $this->competidor1;
    }

    public function setCompetidor1(?Usuario $competidor1): self
    {
        $this->competidor1 = $competidor1;

        return $this;
    }

    public function getCompetidor2(): ?Usuario
    {
        return $this->competidor2;
    }

    public function setCompetidor2(?Usuario $competidor2): self
    {
        $this->competidor2 = $competidor2;

        return $this;
    }

    public function getJornada(): ?int
    {
        return $this->jornada;
    }

    public function setJornada(int $jornada): self
    {
        $this->jornada = $jornada;

        return $this;
    }

}