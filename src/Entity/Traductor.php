<?php

namespace App\Entity;

use App\DTO\TraductorDTO;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\TranslateRepository::class)]
#[ORM\Table(name: 'traductor')]
class Traductor
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 2)]
    private $locale;

    /**
     * @var array
     */
    #[ORM\Column(type: 'json', length: 2)]
    private $textos;

    private $traductorDTO;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $locale
     * @return Traductor
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param array $textos
     * @return Traductor
     */
    public function setTextos($textos)
    {
        $this->textos = $textos;

        return $this;
    }

    /**
     * @return array
     */
    public function getTextos()
    {
        return $this->textos;
    }

    /**
     * @return TraductorDTO
     */
    public function getTraductorDTO()
    {
        return $this->traductorDTO;
    }

    /**
     * @param TraductorDTO $traductorDTO
     */
    public function setTraductorDTO($traductorDTO)
    {
        $this->traductorDTO = $traductorDTO;
    }
}
