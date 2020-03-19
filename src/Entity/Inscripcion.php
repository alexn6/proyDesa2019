<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InscripcionRepository")
 */
class Inscripcion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="date")
     */
    private $fecha_ini;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha_cierre;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $monto;
    
    /**
     * @ORM\Column(type="string", length=700, nullable=true)
     */
    private $requisitos;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaIni(): ?\DateTimeInterface
    {
        return $this->fecha_ini;
    }

    public function setFechaIni(\DateTimeInterface $fecha_ini): self
    {
        $this->fecha_ini = $fecha_ini;

        return $this;
    }

    public function getFechaCierre(): ?\DateTimeInterface
    {
        return $this->fecha_cierre;
    }

    public function setFechaCierre(\DateTimeInterface $fecha_cierre): self
    {
        $this->fecha_cierre = $fecha_cierre;

        return $this;
    }

    public function getMonto(): ?int
    {
        return $this->monto;
    }

    public function setMonto(?int $monto): self
    {
        $this->monto = $monto;

        return $this;
    }

    public function getRequisitos(): ?string
    {
        return $this->requisitos;
    }

    public function setRequisitos(?string $requisitos): self
    {
        $this->requisitos = $requisitos;

        return $this;
    }
    
}