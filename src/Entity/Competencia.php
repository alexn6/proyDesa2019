<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use App\Utils\Constant;

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
     * @ORM\Column(type="string", columnDefinition="ENUM('MASCULINO', 'FEMENINO', 'MIXTO')")
     */
    private $genero;

    /**
     * @ORM\Column(type="integer",  nullable=true)
     */
    private $max_competidores;

    /**
     * @ORM\Column(type="integer",  nullable=true)
     */
    private $cant_grupos;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Categoria")
     * @ORM\JoinColumn(name="categoria_id", referencedColumnName="id")
     */
    private $categoria;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TipoOrganizacion")
     * @ORM\JoinColumn(name="organizacion_id", referencedColumnName="id")
     */
    private $organizacion;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UsuarioCompetencia", mappedBy="competencia")
     */
    private $usuarioscompetencias;

    /**
     * @ORM\Column(type="integer",  nullable=true)
     */
    private $fase;

    /**
     * @ORM\Column(type="integer",  nullable=true)
     */
    private $min_competidores;

    /**
     * @ORM\Column(type="integer")
     */
    private $fase_actual;

    /**
     * @ORM\Column(type="integer")
     */
    private $frec_dias;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('COMPETENCIA_SIN_INSCRIPCION', 'COMPETENCIA_INSCRIPCION_ABIERTA', 'COMPETENCIA_INICIADA', 'COMPETENCIA_FINALIZADA')")
     */
    private $estado;

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
        if (!in_array($genero, array(Constant::GENERO_MASCULINO, Constant::GENERO_FEMENINO, Constant::GENERO_MIXTO))) {
            throw new \InvalidArgumentException("Genero invalido");
        }
        $this->genero = $genero;

        return $this;        
    }

    public function getCantGrupos(): ?int
    {
        return $this->cant_grupos;
    }

    public function setCantGrupos(int $cant_grupos): self
    {
        $this->cant_grupos = $cant_grupos;

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

    public function getOrganizacion(): ?TipoOrganizacion
    {
        return $this->organizacion;
    }

    public function setOrganizacion(?TipoOrganizacion $organizacion): self
    {
        $this->organizacion = $organizacion;

        return $this;
    }

    public function getFase(): ?int
    {
        return $this->fase;
    }

    public function setFase(int $fase): self
    {
        $this->fase = $fase;

        return $this;
    }

    public function getMinCompetidores(): ?int
    {
        return $this->min_competidores;
    }

    public function setMinCompetidores(int $min_competidores): self
    {
        $this->min_competidores = $min_competidores;

        return $this;
    }

    public function getFaseActual(): ?int
    {
        return $this->fase_actual;
    }

    public function setFaseActual(int $fase_actual): self
    {
        $this->fase_actual = $fase_actual;

        return $this;
    }

    public function getFrecDias(): ?int
    {
        return $this->frec_dias;
    }

    public function setFrecDias(int $frec_dias): self
    {
        $this->frec_dias = $frec_dias;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        if (!in_array($estado, array(Constant::ESTADO_SIN_INSCRIPCION, Constant::ESTADO_INSCRIPCION_ABIERTA, Constant::ESTADO_INICIADA, Constant::ESTADO_FINALIZADA))) {
            throw new \InvalidArgumentException("Estado invalido");
        }
        $this->estado = $estado;

        return $this;        
    }

}