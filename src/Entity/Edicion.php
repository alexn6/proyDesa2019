<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use App\Utils\Constant;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EdicionRepository")
 */
class Edicion
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
    private $operacion;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $editor;

    /**
     * @ORM\Column(type="date", nullable=false)
     */
    private $fecha;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Encuentro")
     * @ORM\JoinColumn(name="encuentro_id", referencedColumnName="id")
     */
    private $encuentro;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEditor(): ?string
    {
        return $this->editor;
    }

    public function setEditor(string $editor): self
    {
        $this->editor = $editor;

        return $this;
    }

    public function getEncuentro(): ?Encuentro
    {
        return $this->encuentro;
    }

    public function setEncuentro(?Encuentro $encuentro): self
    {
        $this->encuentro = $encuentro;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(?\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getOperacion(): ?string
    {
        return $this->operacion;
    }

    public function setOperacion(string $operacion): self
    {
        $this->operacion = $operacion;

        return $this;
    }

    

}