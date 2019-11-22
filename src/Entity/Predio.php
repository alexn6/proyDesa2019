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
     * @ORM\ManyToOne(targetEntity="App\Entity\Competencia")
     * @ORM\JoinColumn(name="competencia_id", referencedColumnName="id")
     */
    private $competencia;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nombre;
    
    /**
     * @ORM\Column(type="string", length=150)
     */
    private $direccion;
    
    /**
     * @ORM\Column(type="string", length=150)
     */
    // ############# despues cambiar por la entidad Ciudad #############
    private $ciudad;

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

    public function getCiudad(): ?string
    {
        return $this->ciudad;
    }

    public function setCiudad(string $ciudad): self
    {
        $this->ciudad = $ciudad;

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