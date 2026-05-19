<?php

namespace App\Entity\Property;

use Doctrine\ORM\Mapping as ORM;

trait CoordinatesProperty
{
    /**
     * @var float
     */
    #[ORM\Column(type: 'float', precision: 24, scale: 4, nullable: true)]
    private $latitud;

    /**
     * @var float
     */
    #[ORM\Column(type: 'float', precision: 24, scale: 4, nullable: true)]
    private $longitud;

    /**
     * @param string $latitud
     */
    public function setLatitud($latitud)
    {
        $this->latitud = $latitud;
    }

    /**
     * @return string
     */
    public function getLatitud()
    {
        return $this->latitud;
    }

    /**
     * @param string $longitud
     */
    public function setLongitud($longitud)
    {
        $this->longitud = $longitud;
    }

    /**
     * @return string
     */
    public function getLongitud()
    {
        return $this->longitud;
    }
}
