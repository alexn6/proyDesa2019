<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use App\Utils\Constant;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InvitacionRepository")
 */
class Invitacion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UsuarioCompetencia")
     * @ORM\JoinColumn(name="uorganizador_id", referencedColumnName="id", nullable=false, onDelete="CASCADE"))
     */
    private $usuarioCompOrg;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Usuario")
     * @ORM\JoinColumn(name="udestino_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $usuarioDestino;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('ALTA', 'BAJA', 'N/D')")
     */
    private $estado;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        if (!in_array($estado, array(Constant::ESTADO_ALTA, Constant::ESTADO_BAJA, Constant::ESTADO_NO_DEFINIDO))) {
            throw new \InvalidArgumentException("Estado invalido");
        }
        $this->estado = $estado;

        return $this;   
    }

    public function getUsuarioCompOrg(): ?UsuarioCompetencia
    {
        return $this->usuarioCompOrg;
    }

    public function setUsuarioCompOrg(?UsuarioCompetencia $usuarioCompOrg): self
    {
        $this->usuarioCompOrg = $usuarioCompOrg;

        return $this;
    }

    public function getUsuarioDestino(): ?Usuario
    {
        return $this->usuarioDestino;
    }

    public function setUsuarioDestino(?Usuario $usuarioDestino): self
    {
        $this->usuarioDestino = $usuarioDestino;

        return $this;
    }
    
}