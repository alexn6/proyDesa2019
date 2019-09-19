<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsuarioRepository")
 */
class Usuario
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
    private $nombreUsuario;
    
    /**
     * @ORM\Column(type="string", length=127)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $apellido;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $correo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pass;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UsuarioCompetencia", mappedBy="usuario")
     */
    private $usuarioscompetencias;

    public function __construct()
    {
        $this->usuarioscompetencias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreUsuario(): ?string
    {
        return $this->nombre;
    }

    public function setNombreUsuario(string $nombreUsuario): self
    {
        $this->nombreUsuario = $nombreUsuario;

        return $this;
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

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(string $correo): self
    {
        $this->correo = $correo;

        return $this;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function setPass(string $pass): self
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * @return Collection|UsuarioCompetencia[]
     */
    public function getUsuarioscompetencias(): Collection
    {
        return $this->usuarioscompetencias;
    }

    public function addUsuarioscompetencia(UsuarioCompetencia $usuarioscompetencia): self
    {
        if (!$this->usuarioscompetencias->contains($usuarioscompetencia)) {
            $this->usuarioscompetencias[] = $usuarioscompetencia;
            $usuarioscompetencia->setUsuario($this);
        }

        return $this;
    }

    public function removeUsuarioscompetencia(UsuarioCompetencia $usuarioscompetencia): self
    {
        if ($this->usuarioscompetencias->contains($usuarioscompetencia)) {
            $this->usuarioscompetencias->removeElement($usuarioscompetencia);
            // set the owning side to null (unless already changed)
            if ($usuarioscompetencia->getUsuario() === $this) {
                $usuarioscompetencia->setUsuario(null);
            }
        }

        return $this;
    }

}