<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JuezCompetenciaRepository")
 */
class JuezCompetencia
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Juez", inversedBy="juezcompetencia")
     * @ORM\JoinColumn(name="id_juez", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $juez;

    /**
     * @ORM\ManyToOne(targetEntity="Competencia", inversedBy="juezcompetencia")
     * @ORM\JoinColumn(name="id_competencia", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $competencia;

    public function getId(): ?int
    {
        return $this->id;
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