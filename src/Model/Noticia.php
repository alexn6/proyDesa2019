<?php

namespace App\Model;

use Serializable;

use Google\Cloud\Core\Timestamp;

class Noticia implements Serializable
{
    private $id;
    private $competition;
    private $title;
    private $resume;
    private $descripcion;
    private $uptime;

    // public function __construct() {}
    
    public function __construct($id, $competition, $title, $resume, $descripcion, $uptime) {
        $this->id = $id;
        $this->competition = $competition;
        $this->title = $title;
        $this->resume = $resume;
        $this->descripcion = $descripcion;
        $this->uptime = $uptime;
	}

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getCompetition(): ?string
    {
        return $this->competition;
    }

    public function setCompetition(string $competition): self
    {
        $this->competition = $competition;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(string $resume): self
    {
        $this->resume = $resume;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }
    public function getUptime(): ?Timestamp
    {
        return $this->uptime;
    }

    public function setUptime(Timestamp $uptime): self
    {
        $this->uptime = $uptime;

        return $this;
    }

    /**
     * Representacion String del objeto
     *
     * @return string
     */
    public function serialize()
    {
        return json_encode([
            'competition' => $this->competition,
            'title' => $this->title,
            'resume' => $this->resume,
            'descripcion' => $this->descripcion,
            'uptime' => $this->uptime
        ]);
    }

    /**
     * Construye el objeto
     *
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        $json = json_decode($serialized);
        
        $this->competition = $json->competition;
        $this->title = $json->title;
        $this->resume = $json->resume;
        $this->descripcion = $json->descripcion;
        $this->uptime = $json->uptime;
    }

}