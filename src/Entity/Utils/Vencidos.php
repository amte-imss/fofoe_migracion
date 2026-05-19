<?php

namespace App\Entity\Utils;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\Utils\VencidosRepository::class)]
#[ORM\Table(name: 'vencidos')]
class Vencidos
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'id')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(type: 'text', nullable: true, name: 'numero')]
    private $numero;

    #[ORM\Column(type: 'text', nullable: true, name: 'ooad')]
    private $ooad;

    #[ORM\Column(type: 'text', nullable: true, name: 'ifirmante')]
    private $ifirmante;

    #[ORM\Column(type: 'text', nullable: true, name: 'firma_autoridad_imss_1')]
    private $firmaAutoridadImss1;

    #[ORM\Column(type: 'text', nullable: true, name: 'puesto_firma_autoridad_imss_1')]
    private $puestoFirmaAutoridadImss1;

    #[ORM\Column(type: 'text', nullable: true, name: 'firma_autoridad_imss_2')]
    private $firmaAutoridadImss2;

    #[ORM\Column(type: 'text', nullable: true, name: 'puesto_firma_autoridad_imss_2')]
    private $puestoFirmaAutoridadImss2;

    #[ORM\Column(type: 'text', nullable: true, name: 'nombrefiscal')]
    private $nombrefiscal;

    #[ORM\Column(type: 'text', nullable: true, name: 'nombrecomercial')]
    private $nombrecomercial;

    #[ORM\Column(type: 'text', nullable: true, name: 'fuica')]
    private $fuica;

    #[ORM\Column(type: 'text', nullable: true, name: 'rfc')]
    private $rfc;

    #[ORM\Column(type: 'text', nullable: true, name: 'oubicacion')]
    private $oubicacion;

    #[ORM\Column(type: 'text', nullable: true, name: 'email')]
    private $email;

    #[ORM\Column(type: 'text', nullable: true, name: 'telefono')]
    private $telefono;

    #[ORM\Column(type: 'text', nullable: true, name: 'sector')]
    private $sector;

    #[ORM\Column(type: 'text', nullable: true, name: 'firma_autoridad_institucia3n_educativa_1')]
    private $firmaAutoridadInstitucionEducativa1;

    #[ORM\Column(type: 'text', nullable: true, name: 'puesto_firma_autoridad_institucia3n_educativa_1')]
    private $puestoFirmaAutoridadInstitucionEducativa1;

    #[ORM\Column(type: 'text', nullable: true, name: 'firma_autoridad_institucia3n_educativa_2')]
    private $firmaAutoridadInstitucionEducativa2;

    #[ORM\Column(type: 'text', nullable: true, name: 'puesto_firma_autoridad_institucia3n_educativa_2')]
    private $puestoFirmaAutoridadInstitucionEducativa2;

    #[ORM\Column(type: 'text', nullable: true, name: 'firma_autoridad_institucia3n_educativa_3')]
    private $firmaAutoridadInstitucionEducativa3;

    #[ORM\Column(type: 'text', nullable: true, name: 'puesto_firma_autoridad_institucia3n_educativa_3')]
    private $puestoFirmaAutoridadInstitucionEducativa3;

    #[ORM\Column(type: 'text', nullable: true, name: 'firma_autoridad_institucia3n_educativa_4')]
    private $firmaAutoridadInstitucionEducativa4;

    #[ORM\Column(type: 'text', nullable: true, name: 'puesto_firma_autoridad_institucia3n_educativa_4')]
    private $puestoFirmaAutoridadInstitucionEducativa4;

    #[ORM\Column(type: 'text', nullable: true, name: 'tipo')]
    private $tipo;

    #[ORM\Column(type: 'text', nullable: true, name: 'objetivo_colaboracion')]
    private $objetivoColaboracion;

    #[ORM\Column(type: 'text', nullable: true, name: 'ciclo_academico')]
    private $cicloAcademico;

    #[ORM\Column(type: 'text', nullable: true, name: 'grado')]
    private $grado;

    #[ORM\Column(type: 'text', nullable: true, name: 'disciplina')]
    private $disciplina;

    #[ORM\Column(type: 'text', nullable: true, name: 'nombre_programa')]
    private $nombrePrograma;

    #[ORM\Column(type: 'text', nullable: true, name: 'fecha_firma_de_convenio')]
    private $fechaFirmaDeConvenio;

    #[ORM\Column(type: 'text', nullable: true, name: 'aao')]
    private $aao;

    #[ORM\Column(type: 'text', nullable: true, name: 'tiempo')]
    private $tiempo;

    #[ORM\Column(type: 'text', nullable: true, name: 'vencimiento_del_convenio')]
    private $vencimientoDelConvenio;

    #[ORM\Column(type: 'text', nullable: true, name: 'fecha_rvoe')]
    private $fechaRvoe;

    #[ORM\Column(type: 'text', nullable: true, name: 'vencimiento_rvoe_puede_haber_fecha__no_aplica')]
    private $vencimientoRvoePuedeHaberFechaNoAplica;

    #[ORM\Column(type: 'text', nullable: true, name: 'rvoe_escaneado')]
    private $rvoeEscaneado;

    #[ORM\Column(type: 'text', nullable: true, name: 'fecha_ota')]
    private $fechaOta;

    #[ORM\Column(type: 'text', nullable: true, name: 'vencimiento_ota')]
    private $vencimientoOta;

    #[ORM\Column(type: 'text', nullable: true, name: 'ota_escaneada')]
    private $otaEscaneada;

    #[ORM\Column(type: 'text', nullable: true, name: 'acreditacia3n_para_medicina_comaem')]
    private $acreditacionParaMedicinaComaem;

    #[ORM\Column(type: 'text', nullable: true, name: 'vencimiento_comaem')]
    private $vencimientoComaem;

    #[ORM\Column(type: 'text', nullable: true, name: 'comaem_escaneado')]
    private $comaemEscaneado;

    #[ORM\Column(type: 'text', nullable: true, name: 'convenio_escaneado')]
    private $convenioEscaneado;

    #[ORM\Column(type: 'text', nullable: true, name: 'observaciones')]
    private $observaciones;

    #[ORM\Column(type: 'text', nullable: true, name: 'status')]
    private $status;

    // Getters y setters dinámicos

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param mixed $numero
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    }

    /**
     * @return mixed
     */
    public function getOoad()
    {
        return $this->ooad;
    }

    /**
     * @param mixed $ooad
     */
    public function setOoad($ooad)
    {
        $this->ooad = $ooad;
    }

    /**
     * @return mixed
     */
    public function getIfirmante()
    {
        return $this->ifirmante;
    }

    /**
     * @param mixed $ifirmante
     */
    public function setIfirmante($ifirmante)
    {
        $this->ifirmante = $ifirmante;
    }

    /**
     * @return mixed
     */
    public function getFirmaAutoridadImss1()
    {
        return $this->firmaAutoridadImss1;
    }

    /**
     * @param mixed $firmaAutoridadImss1
     */
    public function setFirmaAutoridadImss1($firmaAutoridadImss1)
    {
        $this->firmaAutoridadImss1 = $firmaAutoridadImss1;
    }

    /**
     * @return mixed
     */
    public function getPuestoFirmaAutoridadImss1()
    {
        return $this->puestoFirmaAutoridadImss1;
    }

    /**
     * @param mixed $puestoFirmaAutoridadImss1
     */
    public function setPuestoFirmaAutoridadImss1($puestoFirmaAutoridadImss1)
    {
        $this->puestoFirmaAutoridadImss1 = $puestoFirmaAutoridadImss1;
    }

    /**
     * @return mixed
     */
    public function getFirmaAutoridadImss2()
    {
        return $this->firmaAutoridadImss2;
    }

    /**
     * @param mixed $firmaAutoridadImss2
     */
    public function setFirmaAutoridadImss2($firmaAutoridadImss2)
    {
        $this->firmaAutoridadImss2 = $firmaAutoridadImss2;
    }

    /**
     * @return mixed
     */
    public function getPuestoFirmaAutoridadImss2()
    {
        return $this->puestoFirmaAutoridadImss2;
    }

    /**
     * @param mixed $puestoFirmaAutoridadImss2
     */
    public function setPuestoFirmaAutoridadImss2($puestoFirmaAutoridadImss2)
    {
        $this->puestoFirmaAutoridadImss2 = $puestoFirmaAutoridadImss2;
    }

    /**
     * @return mixed
     */
    public function getNombrefiscal()
    {
        return $this->nombrefiscal;
    }

    /**
     * @param mixed $nombrefiscal
     */
    public function setNombrefiscal($nombrefiscal)
    {
        $this->nombrefiscal = $nombrefiscal;
    }

    /**
     * @return mixed
     */
    public function getNombrecomercial()
    {
        return $this->nombrecomercial;
    }

    /**
     * @param mixed $nombrecomercial
     */
    public function setNombrecomercial($nombrecomercial)
    {
        $this->nombrecomercial = $nombrecomercial;
    }

    /**
     * @return mixed
     */
    public function getFuica()
    {
        return $this->fuica;
    }

    /**
     * @param mixed $fuica
     */
    public function setFuica($fuica)
    {
        $this->fuica = $fuica;
    }

    /**
     * @return mixed
     */
    public function getRfc()
    {
        return $this->rfc;
    }

    /**
     * @param mixed $rfc
     */
    public function setRfc($rfc)
    {
        $this->rfc = $rfc;
    }

    /**
     * @return mixed
     */
    public function getOubicacion()
    {
        return $this->oubicacion;
    }

    /**
     * @param mixed $oubicacion
     */
    public function setOubicacion($oubicacion)
    {
        $this->oubicacion = $oubicacion;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * @param mixed $telefono
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    }

    /**
     * @return mixed
     */
    public function getSector()
    {
        return $this->sector;
    }

    /**
     * @param mixed $sector
     */
    public function setSector($sector)
    {
        $this->sector = $sector;
    }

    /**
     * @return mixed
     */
    public function getFirmaAutoridadInstitucionEducativa1()
    {
        return $this->firmaAutoridadInstitucionEducativa1;
    }

    /**
     * @param mixed $firmaAutoridadInstitucionEducativa1
     */
    public function setFirmaAutoridadInstitucionEducativa1($firmaAutoridadInstitucionEducativa1)
    {
        $this->firmaAutoridadInstitucionEducativa1 = $firmaAutoridadInstitucionEducativa1;
    }

    /**
     * @return mixed
     */
    public function getPuestoFirmaAutoridadInstitucionEducativa1()
    {
        return $this->puestoFirmaAutoridadInstitucionEducativa1;
    }

    /**
     * @param mixed $puestoFirmaAutoridadInstitucionEducativa1
     */
    public function setPuestoFirmaAutoridadInstitucionEducativa1($puestoFirmaAutoridadInstitucionEducativa1)
    {
        $this->puestoFirmaAutoridadInstitucionEducativa1 = $puestoFirmaAutoridadInstitucionEducativa1;
    }

    /**
     * @return mixed
     */
    public function getFirmaAutoridadInstitucionEducativa2()
    {
        return $this->firmaAutoridadInstitucionEducativa2;
    }

    /**
     * @param mixed $firmaAutoridadInstitucionEducativa2
     */
    public function setFirmaAutoridadInstitucionEducativa2($firmaAutoridadInstitucionEducativa2)
    {
        $this->firmaAutoridadInstitucionEducativa2 = $firmaAutoridadInstitucionEducativa2;
    }

    /**
     * @return mixed
     */
    public function getPuestoFirmaAutoridadInstitucionEducativa2()
    {
        return $this->puestoFirmaAutoridadInstitucionEducativa2;
    }

    /**
     * @param mixed $puestoFirmaAutoridadInstitucionEducativa2
     */
    public function setPuestoFirmaAutoridadInstitucionEducativa2($puestoFirmaAutoridadInstitucionEducativa2)
    {
        $this->puestoFirmaAutoridadInstitucionEducativa2 = $puestoFirmaAutoridadInstitucionEducativa2;
    }

    /**
     * @return mixed
     */
    public function getFirmaAutoridadInstitucionEducativa3()
    {
        return $this->firmaAutoridadInstitucionEducativa3;
    }

    /**
     * @param mixed $firmaAutoridadInstitucionEducativa3
     */
    public function setFirmaAutoridadInstitucionEducativa3($firmaAutoridadInstitucionEducativa3)
    {
        $this->firmaAutoridadInstitucionEducativa3 = $firmaAutoridadInstitucionEducativa3;
    }

    /**
     * @return mixed
     */
    public function getPuestoFirmaAutoridadInstitucionEducativa3()
    {
        return $this->puestoFirmaAutoridadInstitucionEducativa3;
    }

    /**
     * @param mixed $puestoFirmaAutoridadInstitucionEducativa3
     */
    public function setPuestoFirmaAutoridadInstitucionEducativa3($puestoFirmaAutoridadInstitucionEducativa3)
    {
        $this->puestoFirmaAutoridadInstitucionEducativa3 = $puestoFirmaAutoridadInstitucionEducativa3;
    }

    /**
     * @return mixed
     */
    public function getFirmaAutoridadInstitucionEducativa4()
    {
        return $this->firmaAutoridadInstitucionEducativa4;
    }

    /**
     * @param mixed $firmaAutoridadInstitucionEducativa4
     */
    public function setFirmaAutoridadInstitucionEducativa4($firmaAutoridadInstitucionEducativa4)
    {
        $this->firmaAutoridadInstitucionEducativa4 = $firmaAutoridadInstitucionEducativa4;
    }

    /**
     * @return mixed
     */
    public function getPuestoFirmaAutoridadInstitucionEducativa4()
    {
        return $this->puestoFirmaAutoridadInstitucionEducativa4;
    }

    /**
     * @param mixed $puestoFirmaAutoridadInstitucionEducativa4
     */
    public function setPuestoFirmaAutoridadInstitucionEducativa4($puestoFirmaAutoridadInstitucionEducativa4)
    {
        $this->puestoFirmaAutoridadInstitucionEducativa4 = $puestoFirmaAutoridadInstitucionEducativa4;
    }

    /**
     * @return mixed
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * @param mixed $tipo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    /**
     * @return mixed
     */
    public function getObjetivoColaboracion()
    {
        return $this->objetivoColaboracion;
    }

    /**
     * @param mixed $objetivoColaboracion
     */
    public function setObjetivoColaboracion($objetivoColaboracion)
    {
        $this->objetivoColaboracion = $objetivoColaboracion;
    }

    /**
     * @return mixed
     */
    public function getCicloAcademico()
    {
        return $this->cicloAcademico;
    }

    /**
     * @param mixed $cicloAcademico
     */
    public function setCicloAcademico($cicloAcademico)
    {
        $this->cicloAcademico = $cicloAcademico;
    }

    /**
     * @return mixed
     */
    public function getGrado()
    {
        return $this->grado;
    }

    /**
     * @param mixed $grado
     */
    public function setGrado($grado)
    {
        $this->grado = $grado;
    }

    /**
     * @return mixed
     */
    public function getDisciplina()
    {
        return $this->disciplina;
    }

    /**
     * @param mixed $disciplina
     */
    public function setDisciplina($disciplina)
    {
        $this->disciplina = $disciplina;
    }

    /**
     * @return mixed
     */
    public function getNombrePrograma()
    {
        return $this->nombrePrograma;
    }

    /**
     * @param mixed $nombrePrograma
     */
    public function setNombrePrograma($nombrePrograma)
    {
        $this->nombrePrograma = $nombrePrograma;
    }

    /**
     * @return mixed
     */
    public function getFechaFirmaDeConvenio()
    {
        return $this->fechaFirmaDeConvenio;
    }

    /**
     * @param mixed $fechaFirmaDeConvenio
     */
    public function setFechaFirmaDeConvenio($fechaFirmaDeConvenio)
    {
        $this->fechaFirmaDeConvenio = $fechaFirmaDeConvenio;
    }

    /**
     * @return mixed
     */
    public function getAao()
    {
        return $this->aao;
    }

    /**
     * @param mixed $aao
     */
    public function setAao($aao)
    {
        $this->aao = $aao;
    }

    /**
     * @return mixed
     */
    public function getTiempo()
    {
        return $this->tiempo;
    }

    /**
     * @param mixed $tiempo
     */
    public function setTiempo($tiempo)
    {
        $this->tiempo = $tiempo;
    }

    /**
     * @return mixed
     */
    public function getVencimientoDelConvenio()
    {
        return $this->vencimientoDelConvenio;
    }

    /**
     * @param mixed $vencimientoDelConvenio
     */
    public function setVencimientoDelConvenio($vencimientoDelConvenio)
    {
        $this->vencimientoDelConvenio = $vencimientoDelConvenio;
    }

    /**
     * @return mixed
     */
    public function getFechaRvoe()
    {
        return $this->fechaRvoe;
    }

    /**
     * @param mixed $fechaRvoe
     */
    public function setFechaRvoe($fechaRvoe)
    {
        $this->fechaRvoe = $fechaRvoe;
    }

    /**
     * @return mixed
     */
    public function getVencimientoRvoePuedeHaberFechaNoAplica()
    {
        return $this->vencimientoRvoePuedeHaberFechaNoAplica;
    }

    /**
     * @param mixed $vencimientoRvoePuedeHaberFechaNoAplica
     */
    public function setVencimientoRvoePuedeHaberFechaNoAplica($vencimientoRvoePuedeHaberFechaNoAplica)
    {
        $this->vencimientoRvoePuedeHaberFechaNoAplica = $vencimientoRvoePuedeHaberFechaNoAplica;
    }

    /**
     * @return mixed
     */
    public function getRvoeEscaneado()
    {
        return $this->rvoeEscaneado;
    }

    /**
     * @param mixed $rvoeEscaneado
     */
    public function setRvoeEscaneado($rvoeEscaneado)
    {
        $this->rvoeEscaneado = $rvoeEscaneado;
    }

    /**
     * @return mixed
     */
    public function getFechaOta()
    {
        return $this->fechaOta;
    }

    /**
     * @param mixed $fechaOta
     */
    public function setFechaOta($fechaOta)
    {
        $this->fechaOta = $fechaOta;
    }

    /**
     * @return mixed
     */
    public function getVencimientoOta()
    {
        return $this->vencimientoOta;
    }

    /**
     * @param mixed $vencimientoOta
     */
    public function setVencimientoOta($vencimientoOta)
    {
        $this->vencimientoOta = $vencimientoOta;
    }

    /**
     * @return mixed
     */
    public function getOtaEscaneada()
    {
        return $this->otaEscaneada;
    }

    /**
     * @param mixed $otaEscaneada
     */
    public function setOtaEscaneada($otaEscaneada)
    {
        $this->otaEscaneada = $otaEscaneada;
    }

    /**
     * @return mixed
     */
    public function getAcreditacionParaMedicinaComaem()
    {
        return $this->acreditacionParaMedicinaComaem;
    }

    /**
     * @param mixed $acreditacionParaMedicinaComaem
     */
    public function setAcreditacionParaMedicinaComaem($acreditacionParaMedicinaComaem)
    {
        $this->acreditacionParaMedicinaComaem = $acreditacionParaMedicinaComaem;
    }

    /**
     * @return mixed
     */
    public function getVencimientoComaem()
    {
        return $this->vencimientoComaem;
    }

    /**
     * @param mixed $vencimientoComaem
     */
    public function setVencimientoComaem($vencimientoComaem)
    {
        $this->vencimientoComaem = $vencimientoComaem;
    }

    /**
     * @return mixed
     */
    public function getComaemEscaneado()
    {
        return $this->comaemEscaneado;
    }

    /**
     * @param mixed $comaemEscaneado
     */
    public function setComaemEscaneado($comaemEscaneado)
    {
        $this->comaemEscaneado = $comaemEscaneado;
    }

    /**
     * @return mixed
     */
    public function getConvenioEscaneado()
    {
        return $this->convenioEscaneado;
    }

    /**
     * @param mixed $convenioEscaneado
     */
    public function setConvenioEscaneado($convenioEscaneado)
    {
        $this->convenioEscaneado = $convenioEscaneado;
    }

    /**
     * @return mixed
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * @param mixed $observaciones
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    
}
