<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PredioCompetenciaRepository")
 */
class PredioCompetencia
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Predio", inversedBy="prediocompetencia")
     * @ORM\JoinColumn(name="id_predio", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $predio;

    /**
     * @ORM\ManyToOne(targetEntity="Competencia", inversedBy="prediocompetencia")
     * @ORM\JoinColumn(name="id_competencia", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $competencia;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPredio(): ?Predio
    {
        return $this->predio;
    }

    public function setPredio(?Predio $predio): self
    {
        $this->predio = $predio;

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