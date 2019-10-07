<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsuarioCompetenciaRepository")
 */
class UsuarioCompetencia
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="usuarioscompetencias")
     * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $usuario;

    /**
     * @ORM\ManyToOne(targetEntity="Competencia", inversedBy="usuarioscompetencias")
     * @ORM\JoinColumn(name="id_competencia", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $competencia;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $alias;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $rol;

    public function getRol(): ?string
    {
        return $this->rol;
    }

    public function setRol(string $rol): self
    {
        $this->rol = $rol;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }
}