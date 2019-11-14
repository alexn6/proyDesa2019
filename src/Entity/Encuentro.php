<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Usuario;
use App\Entity\Competencia;
use App\Entity\Campo;
use App\Entity\Turno;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Jornada")
     * @ORM\JoinColumn(name="jornada_id", referencedColumnName="id")
     */
    private $jornada;

    /**
     * @ORM\Column(type="integer")
     */
    private $rdo_comp1;

    /**
     * @ORM\Column(type="integer")
     */
    private $rdo_comp2;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Juez")
     * @ORM\JoinColumn(name="juez_id", referencedColumnName="id")
     */
    private $juez;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Campo")
     * @ORM\JoinColumn(name="campo_id", referencedColumnName="id")
     */
    private $campo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Turno")
     * @ORM\JoinColumn(name="turno_id", referencedColumnName="id")
     */
    private $turno;

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

    public function getRdoComp1(): ?int
    {
        return $this->rdo_comp1;
    }

    public function setRdoComp1(int $rdo_comp1): self
    {
        $this->rdo_comp1 = $rdo_comp1;

        return $this;
    }

    public function getRdoComp2(): ?int
    {
        return $this->rdo_comp2;
    }

    public function setRdoComp2(int $rdo_comp2): self
    {
        $this->rdo_comp2 = $rdo_comp2;

        return $this;
    }

    public function getJornada(): ?Jornada
    {
        return $this->jornada;
    }

    public function setJornada(?Jornada $jornada): self
    {
        $this->jornada = $jornada;

        return $this;
    }

    public function getJuez(): ?Juez
    {
        return $this->juez;
    }

    public function setJuez(?Juez $juez): self
    {
        $this->juez = $juez;

        return $this;
    }

    public function getCampo(): ?Campo
    {
        return $this->campo;
    }

    public function setCampo(?Campo $campo): self
    {
        $this->campo = $campo;

        return $this;
    }

    public function getTurno(): ?Turno
    {
        return $this->turno;
    }

    public function setTurno(?Turno $turno): self
    {
        $this->turno = $turno;

        return $this;
    }

}