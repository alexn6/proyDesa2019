<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ResultadoRepository")
 */
class Resultado
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UsuarioCompetencia")
     * @ORM\JoinColumn(name="competidor_id", referencedColumnName="id")
     */
    private $competidor;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $jugados;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ganados;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $empatados;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $perdidos;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJugados(): ?int
    {
        return $this->jugados;
    }

    public function setJugados(?int $jugados): self
    {
        $this->jugados = $jugados;

        return $this;
    }

    public function getGanados(): ?int
    {
        return $this->ganados;
    }

    public function setGanados(?int $ganados): self
    {
        $this->ganados = $ganados;

        return $this;
    }

    public function getEmpatados(): ?int
    {
        return $this->empatados;
    }

    public function setEmpatados(?int $empatados): self
    {
        $this->empatados = $empatados;

        return $this;
    }

    public function getPerdidos(): ?int
    {
        return $this->perdidos;
    }

    public function setPerdidos(?int $perdidos): self
    {
        $this->perdidos = $perdidos;

        return $this;
    }

    public function getCompetidor(): ?UsuarioCompetencia
    {
        return $this->competidor;
    }

    public function setCompetidor(?UsuarioCompetencia $competidor): self
    {
        $this->competidor = $competidor;

        return $this;
    }
    
}