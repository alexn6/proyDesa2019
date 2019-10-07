<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompetenciaRepository")
 */
class Competencia
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
     * @ORM\Column(type="date")
     */
    private $fecha_ini;

    /**
     * @ORM\Column(type="date")
     */
    private $fecha_fin;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $ciudad;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $genero;

    /**
     * @ORM\Column(type="integer")
     */
    private $max_competidores;

    // /**
    //  * Una competencia tiene una sola categoria
    //  * @ORM\OneToOne(targetEntity="App\Entity\Categoria", inversedBy="competencia")
    //  */
    /**
     * Una competencia tiene una sola categoria
     * @ORM\OneToOne(targetEntity="App\Entity\Categoria")
     */
    private $categoria;

    /**
     * Una competencia tiene un solo tipo de organizacion
     * @ORM\OneToOne(targetEntity="App\Entity\TipoOrganizacion")
     */
    private $organizacion;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UsuarioCompetencia", mappedBy="competencia")
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

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre($nombre): self
    {
        $this->nombre = $nombre;

        return $this;
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

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->fecha_fin;
    }

    public function setFechaFin(\DateTimeInterface $fecha_fin): self
    {
        $this->fecha_fin = $fecha_fin;

        return $this;
    }

    public function getMaxCompetidores(): ?int
    {
        return $this->max_competidores;
    }

    public function setMaxCompetidores(int $max_competidores): self
    {
        $this->max_competidores = $max_competidores;

        return $this;
    }

    public function getCategoria(): ?Categoria
    {
        return $this->categoria;
    }

    public function setCategoria(?Categoria $categoria): self
    {
        $this->categoria = $categoria;

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
            $usuarioscompetencia->setCompetencia($this);
        }

        return $this;
    }

    public function removeUsuarioscompetencia(UsuarioCompetencia $usuarioscompetencia): self
    {
        if ($this->usuarioscompetencias->contains($usuarioscompetencia)) {
            $this->usuarioscompetencias->removeElement($usuarioscompetencia);
            // set the owning side to null (unless already changed)
            if ($usuarioscompetencia->getCompetencia() === $this) {
                $usuarioscompetencia->setCompetencia(null);
            }
        }

        return $this;
    }

    public function getOrganizacion(): ?TipoOrganizacion
    {
        return $this->organizacion;
    }

    public function setOrganizacion(?TipoOrganizacion $organizacion): self
    {
        $this->organizacion = $organizacion;

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

    public function getGenero(): ?string
    {
        return $this->genero;
    }

    public function setGenero(string $genero): self
    {
        $this->genero = $genero;

        return $this;
    }

}