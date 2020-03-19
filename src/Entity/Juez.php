<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Juez;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JuezRepository")
 */
class Juez
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $nombre;
    
    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $apellido;
    
    /**
     * @ORM\Column(type="integer", nullable=false, unique=true)
     */
    private $dni;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JuezCompetencia", mappedBy="juez")
     */
    private $juezcompetencia;

    public function __construct()
    {
        $this->juezcompetencia = new ArrayCollection();
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

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): self
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function getDni(): ?int
    {
        return $this->dni;
    }

    public function setDni(int $dni): self
    {
        $this->dni = $dni;

        return $this;
    }

    /**
     * @return Collection|JuezCompetencia[]
     */
    public function getJuezcompetencia(): Collection
    {
        return $this->juezcompetencia;
    }

    public function addJuezcompetencium(JuezCompetencia $juezcompetencium): self
    {
        if (!$this->juezcompetencia->contains($juezcompetencium)) {
            $this->juezcompetencia[] = $juezcompetencium;
            $juezcompetencium->setJuez($this);
        }

        return $this;
    }

    public function removeJuezcompetencium(JuezCompetencia $juezcompetencium): self
    {
        if ($this->juezcompetencia->contains($juezcompetencium)) {
            $this->juezcompetencia->removeElement($juezcompetencium);
            // set the owning side to null (unless already changed)
            if ($juezcompetencium->getJuez() === $this) {
                $juezcompetencium->setJuez(null);
            }
        }

        return $this;
    }

}