import React from 'react';
import ReactDOM from 'react-dom';
import Select from 'react-select';
import makeAnaimated from 'react-select/animated';
import { useState } from 'react';

document.querySelector('#appbundle_convenio_admin_razon_social').disabled = true;
//codigo para ocultar todos los campos del formulario
document.getElementById('appbundle_convenio_admin_razon_social').style.display = 'none';
document.getElementById('appbundle_convenio_admin_institucion').style.display = 'none';
document.getElementById('appbundle_convenio_admin_nombre').style.display = 'none';
document.getElementById('appbundle_convenio_admin_tipo').style.display = 'none';
document.getElementById('appbundle_convenio_admin_convenio_general').style.display = 'none';
document.getElementById('appbundle_convenio_admin_delegacion').style.display = 'none';
document.getElementById('appbundle_convenio_admin_sector').style.display = 'none';
document.getElementById('appbundle_convenio_admin_objetivo_colaboracion').style.display = 'none';
document.getElementById('appbundle_convenio_admin_cicloAcademico').style.display = 'none';
document.getElementById('appbundle_convenio_admin_nivelAcademico').style.display = 'none';
document.getElementById('appbundle_convenio_admin_disciplina').style.display = 'none';
document.getElementById('appbundle_convenio_admin_carrera').style.display = 'none';
document.getElementById('appbundle_convenio_admin_nombre_programa').style.display = 'none';
document.getElementById('appbundle_convenio_admin_fecha_firma').style.display = 'none';
document.getElementById('appbundle_convenio_admin_vigencia_text').style.display = 'none';
document.getElementById('appbundle_convenio_admin_fecha_vence_convenio').style.display = 'none';
document.getElementById('appbundle_convenio_admin_fecha_ota').style.display = 'none';
document.getElementById('appbundle_convenio_admin_fecha_vence_ota').style.display = 'none';
document.getElementById('appbundle_convenio_admin_fecha_rvoe').style.display = 'none';
document.getElementById('appbundle_convenio_admin_fecha_vence_rvoe').style.display = 'none';
document.getElementById('appbundle_convenio_admin_fecha_emite_comaem').style.display = 'none';
document.getElementById('appbundle_convenio_admin_fecha_vence_comaem').style.display = 'none';
document.getElementById('appbundle_convenio_admin_cargoFirmaIdFirst').style.display = 'none';
document.getElementById('appbundle_convenio_admin_cargo_emite_signfirst').style.display = 'none';
document.getElementById('appbundle_convenio_admin_recibe_signfirst').style.display = 'none';
document.getElementById('appbundle_convenio_admin_nombre_emite_signfirst').style.display = 'none';
document.getElementById('appbundle_convenio_admin_cargoFirmaIdSecond').style.display = 'none';
document.getElementById('appbundle_convenio_admin_cargo_emite_signsecond').style.display = 'none';
document.getElementById('appbundle_convenio_admin_recibe_signsecond').style.display = 'none';
document.getElementById('appbundle_convenio_admin_nombre_emite_signsecond').style.display = 'none';
document.getElementById('sendConvenioAdmin').style.display = 'none';
//ocultar etiquetas de cada componenete 
document.getElementById('labelrazonsocial').style.display = 'none';
document.getElementById('nombreConvenioAdminLbl').style.display = 'none';
document.getElementById('convenioGralId').style.display = 'none';
document.getElementById('sectorAdminLblId').style.display = 'none';
document.getElementById('objAdminLblId').style.display = 'none';
document.getElementById('labeltipo').style.display = 'none';
document.getElementById('conveniolabel').style.display = 'none';
document.getElementById('motivoAdminLblId').style.display = 'none';
document.getElementById('appbundle_convenio_nivelAcademicoL').style.display = 'none';
document.getElementById('disciplinaAdminLblId').style.display = 'none';
document.getElementById('carreraAdminLblId').style.display = 'none';
document.getElementById('nomProAdminLblId').style.display = 'none';
document.getElementById('fechaFirmaAdminLblId').style.display = 'none';
document.getElementById('vigenciaAnios').style.display = 'none';
document.getElementById('jobSignImssAdminUno').style.display = 'none';
document.getElementById('jobSignInstAdminUno').style.display = 'none';
document.getElementById('labelooad').style.display = 'none';
document.getElementById('labelfecvigen1').style.display = 'none';
document.getElementById('labelfechaota').style.display = 'none';
document.getElementById('labelota2').style.display = 'none';
document.getElementById('labelrvoe').style.display = 'none';
document.getElementById('labelrvoe2').style.display = 'none';
document.getElementById('labelcomaem').style.display = 'none';
document.getElementById('labelcomaem2').style.display = 'none';
document.getElementById('labelimss1').style.display = 'none';
document.getElementById('labelfirmante1').style.display = 'none';
document.getElementById('labelimss2').style.display = 'none';
document.getElementById('labelfirmante2').style.display = 'none';
document.getElementById('labelfirmaimss2').style.display = 'none';
document.getElementById('labelnombrefir2').style.display = 'none';
document.getElementById('etiquetacamposreque').style.display = 'none';


//codigo para deshabilitar todos los campos del formulario nuevo convenio
document.querySelector('#appbundle_convenio_admin_nombre').disabled = true;
document.querySelector('#appbundle_convenio_admin_tipo').disabled = true;
document.querySelector('#appbundle_convenio_admin_convenio_general').disabled = true;
document.querySelector('#appbundle_convenio_admin_delegacion').disabled = true;
document.querySelector('#appbundle_convenio_admin_sector').disabled = true;
document.querySelector('#appbundle_convenio_admin_objetivo_colaboracion').disabled = true;
document.querySelector('#appbundle_convenio_admin_cicloAcademico').disabled = true;
document.querySelector('#appbundle_convenio_admin_nivelAcademico').disabled = true;
document.querySelector('#appbundle_convenio_admin_disciplina').disabled = true;
document.querySelector('#appbundle_convenio_admin_carrera').disabled = true;
document.querySelector('#appbundle_convenio_admin_nombre_programa').disabled = true;
document.querySelector('#appbundle_convenio_admin_fecha_firma').disabled = true;
document.querySelector('#appbundle_convenio_admin_vigencia_text').disabled = true;
document.querySelector('#appbundle_convenio_admin_fecha_vence_convenio').disabled = true;
document.querySelector('#appbundle_convenio_admin_fecha_ota').disabled = true;
document.querySelector('#appbundle_convenio_admin_fecha_vence_ota').disabled = true;
document.querySelector('#appbundle_convenio_admin_fecha_rvoe').disabled = true;
document.querySelector('#appbundle_convenio_admin_fecha_vence_rvoe').disabled = true;
document.querySelector('#appbundle_convenio_admin_fecha_emite_comaem').disabled = true;
document.querySelector('#appbundle_convenio_admin_fecha_vence_comaem').disabled = true;
document.querySelector('#appbundle_convenio_admin_cargoFirmaIdFirst').disabled = true;
document.querySelector('#appbundle_convenio_admin_cargo_emite_signfirst').disabled = true;
document.querySelector('#appbundle_convenio_admin_recibe_signfirst').disabled = true;
document.querySelector('#appbundle_convenio_admin_nombre_emite_signfirst').disabled = true;
document.querySelector('#appbundle_convenio_admin_cargoFirmaIdSecond').disabled = true;
document.querySelector('#appbundle_convenio_admin_cargo_emite_signsecond').disabled = true;
document.querySelector('#appbundle_convenio_admin_recibe_signsecond').disabled = true;
document.querySelector('#appbundle_convenio_admin_nombre_emite_signsecond').disabled = true;
document.querySelector('#sendConvenioAdmin').disabled = true;

const optionsOOAD =[
    {value:"1", label: "Aguascalientes"},
    {value:"2", label: "Baja California"},
    {value:"3", label: "Baja California Sur"},
    {value:"4", label: "Campeche"},
    {value:"5", label: "Coahuila"},
    {value:"6", label: "Colima"},
    {value:"7", label: "Chiapas"},
    {value:"8", label: "Chihuahua"},
    {value:"9", label:"Oficinas Centrales"},
    {value:"10", label: "Durango"},
    {value:"11", label: "Guanajuato"},
    {value:"12", label: "Guerrero"},
    {value:"13", label: "Hidalgo"},
    {value:"14", label: "Jalisco"},
    {value:"15", label: "Edo Méx Ote"},
    {value:"16", label: "Edo Méx Pte"},
    {value:"17", label:"Michoacán"},
    {value:"18", label:"Morelos"},
    {value:"19", label:"Nayarit"},
    {value:"20", label:"Nuevo León"},
    {value:"21", label:"Oaxaca"},
    {value:"22", label:"Puebla"},
    {value:"23", label:"Querétaro"},
    {value:"24", label:"Quintana Roo"},
    {value:"25", label:"San Luis Potosí"},
    {value:"26", label:"Sinaloa"},
    {value:"27", label:"Sonora"},
    {value:"28", label:"Tabasco"},
    {value:"29", label:"Tamaulipas"},
    {value:"30", label:"Tlaxcala"},
    {value:"31", label:"Veracruz Norte"},
    {value:"32", label:"Veracruz Sur"},
    {value:"33", label:"Yucatán"},
    {value:"34", label:"Zacatecas"},
    {value:"35", label:"D F 1 Norte"},
    {value:"36", label:"D F 2 Norte"},
    {value:"37", label:"D F 3 Sur"},
    {value:"38", label:"D F 4 Sur"},
    {value:"39", label:"Mando"},
]

const optionsMotivos =[
    {value:"1", label: "1. Ciclo clínico"},
    {value:"2", label: "2. Internado médico"},
    {value:"3", label: "3. Servicio social"},
    {value:"4", label: "4. Rotación parcial"},
    {value:"5", label: "5. Aval académico Educación permanente"},
    {value:"6", label: "6. Aval académico Posgrado"},
    {value:"7", label: "7. Recursos para el docente y/o directivo"}
]

const animatedComponents = makeAnaimated()

function NewComponent() {
    const [isVisible, setIsVisible] = useState(false);
    const [mostrarFechasComaem, setMostrarFechasComaem] = useState(true);

    function handleCheckboxChange(selectedOption) {
        const checkboxSi = document.getElementById('cartaIntencionSi');
        const checkboxNo = document.getElementById('cartaIntencionNo');
        
        if (selectedOption === 'si') {
            checkboxNo.checked = false;
            setMostrarFechasComaem(false); // Ocultar el div
        } else if (selectedOption === 'no') {
            checkboxSi.checked = false;
            setMostrarFechasComaem(true); // Mostrar el div
        }
    }

    return (
        <>
            {/* Primer bloque input rfc y el botón buscar */}
            <div className="container containerCstmWidth">             
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-4">
                        <label htmlFor="rfc-input" className="text-success">* R.F.C. de Convenio</label>
                        <input id="appbundle_convenio_admin_rfc" name="appbundle_convenio_admin[rfc]" type="text" className="form-control" placeholder="Escribe una R.F.C." required/>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button onClick={() => setIsVisible(true)} id="appbundle_convenio_admin_buscar" name="appbundle_convenio_admin[buscar]" class="btn btn-success btn-block">Buscar RFC</button>
                    </div>
                    <div className='col-md-4'></div>
                </div>
            </div>
            {/* Segundo bloque cuatro inputs */}
            <div id="contenedor_form_convenio" className="container containerCstmWidth mt-3" style={{ backgroundColor: 'white', display: isVisible ? 'block' : 'none' }}>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-12">
                        <label id="labelrazonsocial" htmlFor="selectField">Razón Social</label>
                        <input id="appbundle_convenio_admin_razon_social" name="appbundle_convenio_admin_razon_social" type="text" className="form-control mb-2"/>
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-6">
                        <label id="nombreConvenioAdminLbl" htmlFor="selectField">* Nombre Comercial</label>
                        <input id="appbundle_convenio_admin_nombre" name="appbundle_convenio_admin_nombre" type="text" className="form-control mb-2" required/>
                    </div>
                    <div className="col-md-6">
                        <label id="conveniolabel" htmlFor="selectField">Institución</label>
                        <select className="form-control mb-2" id="appbundle_convenio_admin_institucion" name="appbundle_convenio_admin_institucion">
                            <option value="">Selecciona una opción</option>
                        </select>
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-6">
                        <label id="labeltipo" htmlFor="selectField">Tipo</label>
                        <select className="form-control mb-2" id="appbundle_convenio_admin_tipo" name="appbundle_convenio_admin_tipo">
                            <option value="" selected>Selecciona Tipo de Convenio</option>
                            <option value="General" selected>General</option>
                            <option value="Específico" selected>Específico</option>
                        </select>
                    </div>
                    <div className="col-md-6">
                        <label id="convenioGralId" htmlFor="selectField">Convenio General</label>
                        <select className="form-control mb-2" id="appbundle_convenio_admin_convenio_general" name="appbundle_convenio_admin_convenio_general">
                            <option value="" selected>Selecciona el Convenio General</option>
                        </select>
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-6">
                        <label id="labelooad" htmlFor="selectField">OOAD</label>
                        <Select id="appbundle_convenio_admin_delegacion" name="appbundle_convenio_admin_delegacion" closeMenuOnSelect={false} components={animatedComponents} isMulti options={optionsOOAD} placeholder="Selecciona una OOAD o varias" onChange={(selectedOption)=>{console.log(selectedOption)}}></Select>
                    </div>
                    <div className="col-md-6">
                        <label id="sectorAdminLblId" htmlFor="selectField">* Sector</label>
                        <select className="form-control mb-2" id="appbundle_convenio_admin_sector" name="appbundle_convenio_admin_sector" required>
                            <option value="" selected>Selecciona el Sector</option>
                            <option value="Público">Público</option>
                            <option value="Privado">Privado</option>
                        </select>
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-12">
                        <label id="objAdminLblId" htmlFor="textareaField">* Objetivo de Colaboración</label>
                        <textarea id="appbundle_convenio_admin_objetivo_colaboracion" name="appbundle_convenio_admin_objetivo_colaboracion" className="form-control mb-2" placeholder="Escribe aquí" required></textarea>
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-6">
                        <label id="motivoAdminLblId" htmlFor="selectField">Motivo de la Colaboración</label>
                        <Select id="appbundle_convenio_admin_cicloAcademico" name="appbundle_convenio_admin[cicloAcademico]" closeMenuOnSelect={false} components={animatedComponents} isMulti options={optionsMotivos} placeholder="Selecciona un Motivo o varios" onChange={(selectedOption)=>{console.log(selectedOption)}}></Select>
                    </div>
                    <div className="col-md-6">
                        <label id="appbundle_convenio_nivelAcademicoL" htmlFor="selectField">Nivel o grado Académico</label>
                        <select className="form-control mb-2" id="appbundle_convenio_admin_nivelAcademico" name="appbundle_convenio_admin[nivelAcademico]">
                            <option value="" selected>Selecciona Nivel o grado Académico</option>
                            <option value="1">Licenciatura</option>
                            <option value="2">Técnico</option>
                            <option value="3">Bachillerato</option>
                            <option value="4">Posgrado</option>
                            <option value="5">Educación Continua</option>
                            <option value="6">Formacion Docente o Directiva</option>
                            <option value="7">Técnico Superior Universitario</option>
                            <option value="8">Otro(describir)</option>
                            <option value="9">Formación docente o directiva</option>
                            <option value="10">Bachillerato Tecnológico</option>
                            <option value="11">Profesional Técnico</option>
                            <option value="12">Bachillerato Tecnológico Profesional en la carrera de</option>
                            <option value="13">Técnico Profesional</option>
                            <option value="14">Postécnico</option>
                            <option value="15">Especialidad</option>
                        </select>
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-6">
                        <label id="disciplinaAdminLblId" htmlFor="selectField">* Disciplina</label>
                        <select className="form-control mb-2" id="appbundle_convenio_admin_disciplina" name="appbundle_convenio_admin[disciplina]" required>
                            <option value="" selected>Selecciona Disciplina</option>
                            <option value="1">Medicina</option>
                            <option value="2">Enfermería</option>
                            <option value="3">Radiología</option>
                            <option value="4">Rehabilitación</option>
                            <option value="5">Odontología</option>
                            <option value="6">Oftalmología</option>
                            <option value="7">Nutrición</option>
                            <option value="8">Psicología</option>
                            <option value="9">Químicos</option>
                            <option value="10">Trabajo Social</option>
                            <option value="11">Otra(describir)</option>
                            <option value="12">Ciclos Clínicos</option>
                            <option value="13">Médico</option>
                        </select>
                    </div>
                    <div className="col-md-6">
                        <label id="carreraAdminLblId" htmlFor="selectField">* Carrera</label>
                        <select className="form-control mb-2" id="appbundle_convenio_admin_carrera" name="appbundle_convenio_admin[carrera]" required>
                            <option value="" selected>Selecciona Carrera</option>
                            <option value="1">Estomatología</option>
                            <option value="2">Farmacia</option>
                            <option value="3">Fisioterapia</option>
                            <option value="4">Gerontología</option>
                            <option value="5">Medicina</option>
                            <option value="6">Nutrición</option>
                            <option value="7">Odontología</option>
                            <option value="8">Optometría</option>
                            <option value="9">Psicología</option>
                            <option value="10">Psicología Clínica</option>
                            <option value="11">Químico Biólogo Parasitólogo</option>
                            <option value="12">Químico Clínico</option>
                            <option value="13">Químico Clínico Bíologo</option>
                            <option value="14">Químico Farmaco Biólogo</option>
                            <option value="15">Radiología e Imagen</option>
                            <option value="16">Terapia Física</option>
                            <option value="17">Trabajo Social</option>
                            <option value="18">Enfermería</option>
                            <option value="19">Dental</option>
                            <option value="20">Radiología e Imagen</option>
                            <option value="21">Laboratorista Clínico</option>
                            <option value="22">Laboratorista Químico</option>
                            <option value="23">Terapia Respiratoria</option>
                            <option value="24">Trabajo Social</option>
                            <option value="25">Enfermería</option>
                        </select>
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-6">
                        <label id="nomProAdminLblId" htmlFor="selectField">* Nombre del Programa</label>
                        <input type="text" className="form-control mb-2" id="appbundle_convenio_admin_nombre_programa" name="appbundle_convenio_admin[nombre_programa]" required/>
                    </div>
                    <div className="col-md-6">
                        <label id="fechaFirmaAdminLblId" htmlFor="selectField">* Fecha de Firma</label>
                        <input type="date" id="appbundle_convenio_admin_fecha_firma" name="appbundle_convenio_admin[fecha_firma]" className="form-control mb-2" required/>
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-6">
                        <label id="vigenciaAnios" htmlFor="selectField">* Vigencia (Años)</label>
                        <select className="form-control mb-2" id="appbundle_convenio_admin_vigencia_text" name="appbundle_convenio_admin[vigencia_text]" required>
                            <option value="" selected>Selecciona Vigencia</option>
                            <option value="1 year">1 año</option>
                            <option value="2 years">2 años</option>
                            <option value="3 years">3 años</option>
                            <option value="4 years">4 años</option>
                            <option value="5 years">5 años</option>
                            <option value="6 years">6 años</option>
                            <option value="7 years">7 años</option>
                            <option value="8 years">8 años</option>
                            <option value="9 years">9 años</option>
                            <option value="10 years">10 años</option>
                            <option value="11 years">11 años</option>
                            <option value="12 years">12 años</option>
                            <option value="13 years">13 años</option>
                            <option value="14 years">14 años</option>
                            <option value="15 years">15 años</option>
                            <option value="16 years">16 años</option>
                            <option value="17 years">17 años</option>
                            <option value="18 years">18 años</option>
                            <option value="19 years">19 años</option>
                            <option value="20 years">20 años</option>
                        </select>
                    </div>
                    <div className="col-md-6">
                        <label id="labelfecvigen1" htmlFor="selectField">Fecha de Vigencia de Convenio</label>
                        <input type="date" id="appbundle_convenio_admin_fecha_vence_convenio" name="appbundle_convenio_admin[fecha_vence_convenio]" className="form-control mb-2" />
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-6">
                        <label id="labelfechaota" htmlFor="selectField">Fecha de Emisión OTA</label>
                        <input type="date" id="appbundle_convenio_admin_fecha_ota" name="appbundle_convenio_admin[fecha_ota]" className="form-control mb-2" />
                    </div>
                    <div className="col-md-6">
                        <label id="labelota2" htmlFor="selectField">Fecha de Vigencia de Emisión OTA</label>
                        <input type="date" id="appbundle_convenio_admin_fecha_vence_ota" name="appbundle_convenio_admin[fecha_vence_ota]" className="form-control mb-2" />
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-6">
                        <label id="labelrvoe" htmlFor="selectField">Fecha de Emisión RVOE</label>
                        <input type="date" id="appbundle_convenio_admin_fecha_rvoe" name="appbundle_convenio_admin[fecha_rvoe]" className="form-control mb-2" />
                    </div>
                    <div className="col-md-6">
                        <label id="labelrvoe2" htmlFor="selectField">Fecha de Vigencia de Emisión RVOE</label>
                        <input type="date" id="appbundle_convenio_admin_fecha_vence_rvoe" name="appbundle_convenio_admin[fecha_vence_rvoe]" className="form-control mb-2" />
                    </div>
                </div>

                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-12">
                        <label>¿Cuentas con una Carta de Intención?</label>
                    </div>
                    <div className="form-check col-md-2" style={{ marginLeft: '40px' }}>
                        <input type="checkbox" className="form-check-input" id="cartaIntencionSi" name="cartaIntencion" onChange={() => handleCheckboxChange('si')} />
                        <label className="form-check-label" htmlFor="cartaIntencionSi">Sí</label>
                    </div>
                    <div className="form-check col-md-2">
                        <input type="checkbox" className="form-check-input" id="cartaIntencionNo" name="cartaIntencion" onChange={() => handleCheckboxChange('no')} />
                        <label className="form-check-label" htmlFor="cartaIntencionNo">No</label>
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '3rem', display: mostrarFechasComaem ? 'flex' : 'none' }} id="contenedor_fechas_comaen">
                    <div className="col-md-6">
                        <label id="labelcomaem" htmlFor="selectField">Fecha de Emisión de Acreditación (COMAEM u otros)</label>
                        <input type="date" id="appbundle_convenio_admin_fecha_emite_comaem" name="appbundle_convenio_admin[fecha_emite_comaem]" className="form-control mb-2" />
                    </div>
                    <div className="col-md-6">
                        <label id="labelcomaem2" htmlFor="selectField">Fecha de Vigencia de Emisión de Acreditación (COMAEM u otros)</label>
                        <input type="date" id="appbundle_convenio_admin_fecha_vence_comaem" name="appbundle_convenio_admin[fecha_vence_comaem]" className="form-control mb-2" />
                    </div>
                </div>

                
                <h3 className="text-center" style={{ marginBottom: '2rem' }}><strong>FIRMANTES</strong></h3>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-6">
                        <h3><strong className="text-secondary">Por el IMSS</strong></h3>
                        <label id="jobSignImssAdminUno" htmlFor="selectField">* Cargo Firmante IMSS 1</label>
                        <select className="form-control mb-2" id="appbundle_convenio_admin_cargoFirmaIdFirst" name="appbundle_convenio_admin[cargoFirmaIdFirst]" required>
                            <option value="" selected>Selecciona Cargo del Firmante</option>
                            <option value="9">1. DPM</option>
                            <option value="4">2. UEI</option>
                            <option value="7">3. CES</option>
                            <option value="11">4. DFRHS</option>
                            <option value="12">5. DEPS</option>
                            <option value="10">6. DDPES</option>
                            <option value="13">7. Titular del OOAD</option>
                            <option value="14">8. JSPM</option>
                            <option value="15">9. CPEI</option>
                            <option value="16">10. CAME</option>
                            <option value="17">11. Director UMAE</option>
                            <option value="18">12. DEIS</option>
                            <option value="19">13. JDES</option>
                        </select>
                    </div>
                    <div className="col-md-6">
                        <h3><strong className="text-secondary">Por la Institución Educativa</strong></h3>
                        <label id="jobSignInstAdminUno" htmlFor="selectField">* Cargo de la Institución Firmante 1</label>
                        <input type="text" className="form-control mb-2" id="appbundle_convenio_admin_cargo_emite_signfirst" name="appbundle_convenio_admin[cargo_emite_signfirst]" required/>
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-6">
                        <label id="labelimss1" htmlFor="selectField">* Nombre del Firmante IMSS 1</label>
                        <input id="appbundle_convenio_admin_recibe_signfirst" name="appbundle_convenio_admin[recibe_signfirst]" type="text" className="form-control mb-2" required/>
                    </div>
                    <div className="col-md-6">
                        <label id="labelfirmante1" htmlFor="selectField">* Nombre Institución del Firmante 1</label>
                        <input id="appbundle_convenio_admin_nombre_emite_signfirst" name="appbundle_convenio_admin[nombre_emite_signfirst]" type="text" className="form-control mb-2" required/>
                    </div>
                </div>

                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-6">
                        <label id="labelimss2" htmlFor="selectField">* Cargo Firmante IMSS 2</label>
                        <select className="form-control mb-2" id="appbundle_convenio_admin_cargoFirmaIdSecond" name="appbundle_convenio_admin[cargoFirmaIdSecond]" required>
                            <option value="" selected>Selecciona Cargo del Firmante</option>
                            <option value="9">1. DPM</option>
                            <option value="4">2. UEI</option>
                            <option value="7">3. CES</option>
                            <option value="11">4. DFRHS</option>
                            <option value="12">5. DEPS</option>
                            <option value="10">6. DDPES</option>
                            <option value="13">7. Titular del OOAD</option>
                            <option value="14">8. JSPM</option>
                            <option value="15">9. CPEI</option>
                            <option value="16">10. CAME</option>
                            <option value="17">11. Director UMAE</option>
                            <option value="18">12. DEIS</option>
                            <option value="19">13. JDES</option>
                        </select>
                    </div>
                    <div className="col-md-6">
                        <label id="labelfirmante2" htmlFor="selectField">* Cargo de la Institución Firmante 2</label>
                        <input id="appbundle_convenio_admin_cargo_emite_signsecond" name="appbundle_convenio_admin[cargo_emite_signsecond]" type="text" className="form-control mb-2" required/>
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-6">
                        <label id="labelfirmaimss2" htmlFor="selectField">* Nombre del Firmante IMSS 2</label>
                        <input id="appbundle_convenio_admin_recibe_signsecond" name="appbundle_convenio_admin[recibe_signsecond]" type="text" className="form-control mb-2" required/>
                    </div>
                    <div className="col-md-6">
                        <label id="labelnombrefir2" htmlFor="selectField">* Nombre Institución del Firmante 2</label>
                        <input id="appbundle_convenio_admin_nombre_emite_signsecond" name="appbundle_convenio_admin[nombre_emite_signsecond]" type="text" className="form-control mb-2" required/>
                    </div>
                </div>
                <div className="row" style={{ marginBottom: '2rem' }}>
                    <div className="col-md-12">
                        <label htmlFor="selectField">Agregar URL de documentos del convenio:</label>
                        <input 
                            placeholder="https://www.imss.gob" 
                            id="url_conveio" 
                            name="url_conveio" 
                            type="text" 
                            className="form-control mb-2" 
                            pattern="https?://.+"
                            title="Por favor ingrese una URL válida que comience con http:// o https://"
                        />
                    </div>
                </div>

                <div id="etiquetacamposreque" className="text-danger" style={{ marginBottom: '2rem' }}>* Campos Obligatorios</div>
                {/* <div className="d-flex justify-content-center">
                    <button id="" name="" className="btn btn-success">Guardar el Convenio</button>
                </div> */}
            </div>

        </>
    );
}
{/*
function handleCheckboxChange(selectedOption) {
    const checkboxSi = document.getElementById('cartaIntencionSi');
    const checkboxNo = document.getElementById('cartaIntencionNo');
    
    if (selectedOption === 'si') {
        checkboxNo.checked = false;
        setMostrarFechasComaem(false); // Ocultar el div
    } else if (selectedOption === 'no') {
        checkboxSi.checked = false;
        setMostrarFechasComaem(true); // Mostrar el div
    }
}*/}
{/*function handleCheckboxChange(selectedOption) {
    const checkboxSi = document.getElementById('cartaIntencionSi');
    const checkboxNo = document.getElementById('cartaIntencionNo');
    
    if (selectedOption === 'si') {
        checkboxNo.checked = false;
    } else if (selectedOption === 'no') {
        checkboxSi.checked = false;
    }
}*/}

document.addEventListener('DOMContentLoaded', () => {
    ReactDOM.render(
        <NewComponent />,
        document.querySelector('.react')
    );
});