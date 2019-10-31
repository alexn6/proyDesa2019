<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TipoOrganizacionRepository")
 */
class TipoOrganizacion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10, unique=true)
     */
    private $codigo;

    /**
     * @ORM\Column(type="string", length=127, unique=true)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descripcion;

    /** Determina el nombre de la funcion a utilizar para controlar el minimo permitido
     * @ORM\Column(type="string", length=127)
     */
    private $minimo;

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

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getMinimo(): ?string
    {
        return $this->minimo;
    }

    public function setMinimo(string $minimo): self
    {
        $this->minimo = $minimo;

        return $this;
    }    

}