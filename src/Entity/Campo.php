<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CampoRepository")
 */
class Campo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Predio")
     * @ORM\JoinColumn(name="predio_id", referencedColumnName="id")
     */
    private $predio;
    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nombre;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $capacidad;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dimensiones;
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
    public function getCapacidad(): ?int
    {
        return $this->capacidad;
    }
    public function setCapacidad(int $capacidad): self
    {
        $this->capacidad = $capacidad;
        return $this;
    }
    public function getDimensiones(): ?int
    {
        return $this->dimensiones;
    }
    public function setDimensiones(int $dimensiones): self
    {
        $this->dimensiones = $dimensiones;
        return $this;
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
}