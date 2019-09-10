<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoriaRepository")
 */
class Categoria
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=127, unique=true)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="integer")
     */
    private $min_integrantes;

     /**
     * @ORM\OneToOne(targetEntity="App\Entity\Deporte", inversedBy="categorias")
     */
    private $deporte;

     /**
     * @ORM\OneToMany(targetEntity="App\Entity\Competencia", mappedBy="categoria")
     */
    private $competencia;

    public function __construct()
    {
        $this->competencia = new ArrayCollection();
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

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getMinIntegrantes(): ?int
    {
        return $this->min_integrantes;
    }

    public function setMinIntegrantes(int $min_integrantes): self
    {
        $this->min_integrantes = $min_integrantes;

        return $this;
    }

    public function getDeporte(): ?Deporte
    {
        return $this->deporte;
    }

    public function setDeporte(?Deporte $deporte): self
    {
        $this->deporte = $deporte;

        return $this;
    }

    /**
     * @return Collection|Competencia[]
     */
    public function getCompetencia(): Collection
    {
        return $this->competencia;
    }

    public function addCompetencium(Competencia $competencium): self
    {
        if (!$this->competencia->contains($competencium)) {
            $this->competencia[] = $competencium;
            $competencium->setCategoria($this);
        }

        return $this;
    }

    public function removeCompetencium(Competencia $competencium): self
    {
        if ($this->competencia->contains($competencium)) {
            $this->competencia->removeElement($competencium);
            // set the owning side to null (unless already changed)
            if ($competencium->getCategoria() === $this) {
                $competencium->setCategoria(null);
            }
        }

        return $this;
    }

}