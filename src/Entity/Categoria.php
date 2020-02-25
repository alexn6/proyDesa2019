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
     * @ORM\Column(type="integer")
     */
    private $duracion_default;    
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Deporte")
     * @ORM\JoinColumn(name="deporte_id", referencedColumnName="id")
     */
    private $deporte;

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

    public function getDuracionDefault(): ?int
    {
        return $this->duracion_default;
    }

    public function setDuracionDefault(int $duracion_default): self
    {
        $this->duracion_default = $duracion_default;

        return $this;
    }
   

}