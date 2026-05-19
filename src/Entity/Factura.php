<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 */
#[ORM\Entity]
#[ORM\Table(name: 'factura')]
class Factura
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
     #[ORM\Column(type: 'string', length: 100, nullable: true)]
     private $zip;

    /**
     * @var DateTime
     */
    #[ORM\Column(type: 'date')]
    private $fechaFacturacion;

    #[ORM\Column(type: 'float', precision: 24, scale: 4, nullable: true)]
     private $monto;

     /**
      * @var string
      */
     #[ORM\Column(type: 'string', length: 100)]
     private $folio;

    /**
     * @Vich\UploadableField(mapping="facturas", fileNameProperty="zip")
     * @var File
     */
    #[Assert\File(maxSize: '2M', mimeTypes: ['application/zip'], mimeTypesMessage: 'Solo se admiten archivos ZIP')]
    private $zipFile;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $aux;

    /**
     * @var Collecction
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\Pago::class, mappedBy: 'factura')]
    private $pagos;

  public function __construct()
  {
    $this->pagos = new ArrayCollection();
  }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $zip
     * @return Factura
     */
     public function setZip($zip)
     {
         $this->zip = $zip;

         return $this;
     }

     /**
      * @return string
      */
     public function getZip()
     {
         return $this->zip;
     }

    /**
     * @return DateTime
     */
    public function getFechaFacturacion()
    {
        return $this->fechaFacturacion;
    }

    /**
     * @param DateTime $fechaFacturacion
     * @return Factura
     */
    public function setFechaFacturacion($fechaFacturacion)
    {
        $this->fechaFacturacion = $fechaFacturacion;

        return $this;
    }

    /**
     * @param float $monto
     * @return Factura
     */
     public function setMonto($monto)
     {
         $this->monto = $monto;

         return $this;
     }

     /**
      * @return float
      */
     public function getMonto()
     {
         return $this->monto;
     }


     /**
     * @param string $folio
     * @return Factura
     */
     public function setFolio($folio)
     {
         $folio = str_replace(".zip", "", $folio);
         $this->folio = $folio;
         return $this;
     }

     /**
      * @return string
      */
     public function getFolio()
     {
         return $this->folio;
     }

    /**
     * @return string
     */
     public function getFechaFacturacionFormatted()
     {
         if($this->getFechaFacturacion()){
             return $this->getFechaFacturacion()->format('d/m/Y');
         }
         return '';
     }

     /**
     * @return File
     */
    public function getZipFile()
    {
        return $this->zipFile;
    }

    /**
     * @param File $zipFile
     */
    public function setZipFile($zipFile = null)
    {
        $this->zipFile = $zipFile;
        $this->setFechaFacturacion(Carbon::now());
    }

    /**
      * @return int
      */
      public function getAux()
      {
          return $this->aux;
      }


      /**
      * @param string $aux
      * @return Factura
      */
      public function setAux($aux)
      {
          $this->aux = $aux;

          return $this;
      }

    /**
     * @return Pago
     */
    public function getPago()
    {
      return $this->getPagos()->first();
    }

    /**
     * @return Collecction
     */
    public function getPagos()
    {
        return $this->pagos;
    }

    /**
     * @param Pago $pago
     * @return Factura
     */
    public function addPago(Pago $pago)
    {
      if(!$this->pagos->contains($pago)) {
        $this->pagos[] = $pago;
        $pago->setFactura($this);
      }

      return $this;
    }

    /**
     * @param Pago $pago
     */
    public function removePago(Pago $pago)
    {
      $this->pagos->removeElement($pago);
    }
}
