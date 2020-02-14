<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NotificationRepository")
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    private $seguidor;

    /**
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    private $competidor;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeguidor(): ?bool
    {
        return $this->seguidor;
    }

    public function setSeguidor(bool $seguidor): self
    {
        $this->seguidor = $seguidor;

        return $this;
    }

    public function getCompetidor(): ?bool
    {
        return $this->competidor;
    }

    public function setCompetidor(bool $competidor): self
    {
        $this->competidor = $competidor;

        return $this;
    }

}