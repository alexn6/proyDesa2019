<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Predio;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PredioRepository")
 */
class Predio
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nombre;
    
    /**
     * @ORM\Column(type="string", length=150)
     */
    private $direccion;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ciudad")
     * @ORM\JoinColumn(name="ciudad_id", referencedColumnName="id")
     */
    private $ciudad;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PredioCompetencia", mappedBy="predio")
     */
    private $prediocompetencia;

    public function __construct()
    {
        $this->prediocompetencia = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(string $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getCiudad(): ?Ciudad
    {
        return $this->ciudad;
    }

    public function setCiudad(?Ciudad $ciudad): self
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    /**
     * @return Collection|PredioCompetencia[]
     */
    public function getPrediocompetencia(): Collection
    {
        return $this->prediocompetencia;
    }

    public function addPrediocompetencium(PredioCompetencia $prediocompetencium): self
    {
        if (!$this->prediocompetencia->contains($prediocompetencium)) {
            $this->prediocompetencia[] = $prediocompetencium;
            $prediocompetencium->setPredio($this);
        }

        return $this;
    }

    public function removePrediocompetencium(PredioCompetencia $prediocompetencium): self
    {
        if ($this->prediocompetencia->contains($prediocompetencium)) {
            $this->prediocompetencia->removeElement($prediocompetencium);
            // set the owning side to null (unless already changed)
            if ($prediocompetencium->getPredio() === $this) {
                $prediocompetencium->setPredio(null);
            }
        }

        return $this;
    }

}