<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DeporteRepository")
 */
class Deporte
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
     * @ORM\Column(type="integer", nullable=false)
     */
    private $puntos_pganado;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $puntos_pempetado;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $puntos_pperdido;

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

    public function getPuntosPganado(): ?int
    {
        return $this->puntos_pganado;
    }

    public function setPuntosPganado(int $puntos_pganado): self
    {
        $this->puntos_pganado = $puntos_pganado;

        return $this;
    }

    public function getPuntosPempetado(): ?int
    {
        return $this->puntos_pempetado;
    }

    public function setPuntosPempetado(int $puntos_pempetado): self
    {
        $this->puntos_pempetado = $puntos_pempetado;

        return $this;
    }

    public function getPuntosPperdido(): ?int
    {
        return $this->puntos_pperdido;
    }

    public function setPuntosPperdido(int $puntos_pperdido): self
    {
        $this->puntos_pperdido = $puntos_pperdido;

        return $this;
    }

}