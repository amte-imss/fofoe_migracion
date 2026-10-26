import React from "react";
import {FormValidator} from "../../../components/FormValidator/FormValidator";
import Select from "react-select";
import makeAnaimated from 'react-select/animated';

export default function ConvenioForm({
                                         formErrors, setFormErrors,
                                         hasConvenios,
                                         handleSubmit, formRef, handleSearchByRfc,
                                         ooads, instituciones, disciplinas,
                                         carreras, cargos,
                                         handleChangeVigenciaText, canFormSubmit,
                                         motivosColaboracion, gradosAcademicos,
                                         handleChangeTipoConvenio, showConveniosGenerales,
                                         handleChandeInstitucion, handleChangeNivelAcademico,
                                         conveniosGenerales, handleChangeCartaIntencion,
                                         showCOMAEM, selectedOoads, setSelectedOoads,
                                         selectedCiclosAcademicos, setSelectedCiclosAcademicos,
                                         handleChangeDisciplina, handleChangeCarrera, maxDateAllowed,
                                         handleChangeTodasOOADs, showSelectOOADs, showSelectCiclosAcademicos,
                                         handleChangeTodosCiclosAcademicos, handleChangeMultiSelect,
                                         useMaxDateAllowed = false,
                                         isCame = false, usuario = {}, isEdit = false,
                                         tipo = '', poblaciones = [], recursos, handleChangeMotivoColaboracion,
                                         handleChangeRecurso
                                     }) {

    const rfcRef = React.useRef();

    const animatedComponents = makeAnaimated()

    const canSearchByRFC = () => {
        return rfcRef.current && rfcRef.current.validity.valid;
    }

    const ooadsOptions = () => {
        return ooads.map(item => {
            return {value: item.id, label: item.nombre};
        });
    }

    const optionsMotivos = () => {
        return motivosColaboracion.map(item => {
            return {value: item.id, label: item.nombre};
        });
    }

    return (<>

        <form onSubmit={handleSubmit} ref={formRef} noValidate autoComplete={'off'}>
            <div className="container">
                <div className="row">
                    <div className="col-md-6">
                        <label htmlFor="rfc-input" className="text-success"><span
                            className={'text-danger'}>*</span> R.F.C. de
                            Convenio</label>
                        <input id="convenio_rfc" name="appbundle_convenio_admin[rfc]" type="text"
                               ref={rfcRef}
                               maxLength={13}
                               minLength={12}
                               className="form-control" placeholder="Escribe una R.F.C." required/>
                        <small className="text-danger">{formErrors['convenio[rfc]']}</small>
                    </div>
                    <div className="col-md-6">
                        <label htmlFor="convenio_buscar">&nbsp;</label>
                        <button id="convenio_buscar"
                                onClick={handleSearchByRfc}
                                type={'button'}
                                className="btn btn-success btn-block">Buscar RFC
                        </button>
                    </div>
                </div>
                {
                    hasConvenios &&
                    <>
                        <div className="row">
                            <div className="col-md-12">
                                <label id="labelrazonsocial" htmlFor="selectField">Razón Social</label>
                                <input id="convenio_razon_social"
                                       required={true}
                                       maxLength={250}
                                       minLength={5}
                                       name="appbundle_convenio_admin[razon_social]" type="text" className="form-control mb-2"/>
                                <small className="text-danger">{formErrors['convenio[razon_social]']}</small>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-6">
                                <label id="nombreConvenioAdminLbl" htmlFor="selectField"><span
                                    className={'text-danger'}>*</span> Nombre Comercial</label>
                                <input id="convenio_nombre"
                                       required
                                       minLength={5}
                                       maxLength={255}
                                       name="appbundle_convenio_admin[nombre]" type="text"
                                       className="form-control mb-2"/>
                                <small className="text-danger">{formErrors['convenio[nombre]']}</small>
                            </div>
                            <div className="col-md-6">
                                <label id="conveniolabel" htmlFor="selectField">Institución</label>
                                <select className="form-control mb-2"
                                        required={true}
                                        onChange={handleChandeInstitucion}
                                        id="convenio_institucion"
                                        name="appbundle_convenio_admin[institucion]">
                                    <option value="">Selecciona una opción</option>
                                    {instituciones.map((item, index) => (
                                        <option value={item.id} key={index}>{item.nombre}</option>))}
                                </select>
                                <small className="text-danger">{formErrors['convenio[institucion]']}</small>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-6">
                                <label id="labeltipo" htmlFor="selectField">Tipo</label>
                                <input type={'hidden'} name="appbundle_convenio_admin[tipo]" value={'Proyecto Especial'}/>
                                <input type={'text'} className="form-control mb-2" id="appbundle_convenio_admin[tipo]"
                                       value={'Proyecto Especial'} readOnly/>
                                <small className="text-danger">{formErrors['convenio[tipo]']}</small>
                            </div>
                            {showConveniosGenerales &&
                                <div className="col-md-6">
                                    <label id="convenioGralId" htmlFor="selectField">Convenio General</label>
                                    <select className="form-control mb-2" id="appbundle_convenio_admin[convenio_general]"
                                            name="appbundle_convenio_admin[convenio_general]">
                                        <option value="">Selecciona el Convenio General</option>
                                        {conveniosGenerales.map((item, index) => (
                                            <option value={item.id} key={index}>{item.nombre}</option>))}
                                    </select>
                                    <small className="text-danger">{formErrors['convenio[convenio_general]']}</small>
                                </div>}
                        </div>
                        {!isCame &&
                            <div className="row">
                                <div className="col-md-6" onInput={handleChangeTodasOOADs}>
                                    <label>¿Para todas la OOADs?</label>
                                    <div className="row">
                                        <div className="form-check col-md-2" style={{marginLeft: '40px'}}>
                                            <input type="radio" className="form-check-input"
                                                   value={1}
                                                   id="todasOOADsSi" name="todasDelegaciones"
                                            />
                                            <label className="form-check-label" htmlFor="todasOOADsSi">Sí</label>
                                        </div>
                                        <div className="form-check col-md-2" style={{marginLeft: '40px'}}>
                                            <input type="radio" className="form-check-input"
                                                   value={0}
                                                   id="todasOOADsNo" name="todasDelegaciones"
                                            />
                                            <label className="form-check-label" htmlFor="todasOOADsNo">No</label>
                                        </div>
                                    </div>
                                </div>
                                {showSelectOOADs &&
                                    <div className="col-md-6">
                                        <label id="labelooad" htmlFor="selectField">OOADs</label>
                                        <Select id="convenio_delegacion" name="appbundle_convenio_admin[delegacion]"
                                                closeMenuOnSelect={false}
                                                required={true}
                                                components={animatedComponents} isMulti
                                                options={ooadsOptions()}
                                                value={selectedOoads}
                                                placeholder="Selecciona una OOAD o varias"
                                                onChange={(selectedOption) => {
                                                    handleChangeMultiSelect('convenio[delegacion]',
                                                        setSelectedOoads,
                                                        selectedOption)
                                                }}
                                        ></Select>
                                        <small className="text-danger">{formErrors['convenio[delegacion]']}</small>
                                    </div>}
                            </div>
                        }
                        {isCame &&
                            <div className="row">
                                <div className="col-md-6">
                                    <label htmlFor="ooad">OOAD</label>
                                    <select id="convenio_delegacion"
                                            className="form-control"
                                            name="delegacion"
                                            required={true}
                                            readOnly
                                            defaultValue={usuario.delegacion.id}
                                    >
                                        <option value="">Seleccionar</option>
                                        <option value={usuario.delegacion.id}>{usuario.delegacion.nombre}</option>
                                    </select>
                                    <small className="text-danger">{formErrors['delegacion']}</small>
                                </div>
                            </div>}
                        <div className="row">
                            <div className="col-md-6">
                                <label id="sectorAdminLblId" htmlFor="selectField"><span
                                    className={'text-danger'}>*</span> Sector</label>
                                <select className="form-control mb-2" id="appbundle_convenio_admin[sector]"
                                        name="appbundle_convenio_admin[sector]" required>
                                    {!isCame && !isEdit && <option value="">Selecciona el Sector</option>}
                                    <option value="Público">Público</option>
                                    {!isCame && !isEdit && <option value="Privado">Privado</option>}
                                </select>
                                <small className="text-danger">{formErrors['convenio[sector]']}</small>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-12">
                                <label id="objAdminLblId" htmlFor="textareaField"><span
                                    className={'text-danger'}>*</span> Objetivo de
                                    Colaboración</label>
                                <textarea id="convenio_objetivo_colaboracion"
                                          name="appbundle_convenio_admin[objetivo_colaboracion]"
                                          className="form-control mb-2"
                                          placeholder="Escribe aquí" required></textarea>
                                <small className="text-danger">{formErrors['convenio[objetivo_colaboracion]']}</small>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-6">
                                <label id="appbundle_convenio_motivo_colaboracion"
                                       htmlFor="convenio_motivo_colaboracion">Motivo Colaboración</label>
                                <select className="form-control mb-2"
                                        id="convenio_motivo_colaboracion"
                                        onChange={handleChangeMotivoColaboracion}
                                        name="appbundle_convenio_admin[motivo_colaboracion]">
                                    <option value="">Selecciona Motivo Colaboración</option>
                                    {
                                        motivosColaboracion.map((item, index) => (
                                            <option value={item.id} key={index}>{item.nombre}</option>))
                                    }
                                </select>
                                <small className="text-danger">{formErrors['convenio[motivo_colaboracion]']}</small>
                            </div>
                            <div className="col-md-6">
                                <label id="appbundle_convenio_recurso"
                                       htmlFor="convenio_motivo_colaboracion">Recurso</label>
                                <select className="form-control mb-2"
                                        id="convenio_recurso"
                                        onChange={handleChangeRecurso}
                                        name="appbundle_convenio_admin[recurso]">
                                    <option value="">Selecciona Recurso</option>
                                    {
                                        recursos.map((item, index) => (
                                            <option value={item.id} key={index}>{item.nombre}</option>))
                                    }
                                </select>
                                <small className="text-danger">{formErrors['convenio[recurso]']}</small>
                            </div>
                        </div>
                        <div className="row">

                            <div className="col-md-6">
                                <label id="appbundle_convenio_poblacion" htmlFor="selectField">Población</label>
                                <select className="form-control mb-2"
                                        id="convenio_poblacion"
                                        name="appbundle_convenio_admin[poblacion]">
                                    <option value="">Selecciona Población</option>
                                    {
                                        poblaciones.map((item, index) => (
                                            <option value={item.id} key={index}>{item.nombre}</option>))
                                    }
                                </select>
                                <small className="text-danger">{formErrors['convenio[poblacion]']}</small>
                            </div>

                            <div className="col-md-6">
                                <label id="appbundle_convenio_nivelAcademicoL" htmlFor="selectField">Nivel o grado
                                    Académico</label>
                                <select className="form-control mb-2"
                                        id="convenio_nivelAcademico"
                                        name="appbundle_convenio_admin[nivelAcademico]">
                                    <option value="">Selecciona Nivel o grado Académico</option>
                                    {
                                        gradosAcademicos.map((item, index) => (
                                            <option value={item.id} key={index}>{item.nombre}</option>))
                                    }
                                </select>
                                <small className="text-danger">{formErrors['convenio[nivelAcademico]']}</small>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-6">
                                <label id="fechaFirmaAdminLblId" htmlFor="selectField"><span
                                    className={'text-danger'}>*</span> Fecha
                                    de Firma</label>
                                <input type="date" id="convenio"
                                       max={useMaxDateAllowed ? maxDateAllowed : null}
                                       name="appbundle_convenio_admin[fecha_firma]" className="form-control mb-2" required/>
                                <small className="text-danger">{formErrors['convenio[fecha_firma]']}</small>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-6">
                                <label id="vigenciaAnios" htmlFor="selectField"><span
                                    className={'text-danger'}>*</span> Vigencia
                                    (Años)</label>
                                <select className="form-control mb-2"
                                        onInput={handleChangeVigenciaText}
                                        id="convenio_vigencia_text"
                                        name="appbundle_convenio_admin[vigencia_text]" required>
                                    <option value="">Selecciona Vigencia</option>

                                    <option value="1m">1 mes</option>
                                    <option value="2m">2 meses</option>
                                    <option value="3m">3 meses</option>
                                    <option value="4m">4 meses</option>
                                    <option value="5m">5 meses</option>
                                    <option value="6m">6 meses</option>
                                    <option value="7m">7 meses</option>
                                    <option value="8m">8 meses</option>
                                    <option value="9m">9 meses</option>
                                    <option value="10m">10 meses</option>
                                    <option value="11m">11 meses</option>
                                    <option value="1">1 año</option>
                                    <option value="2">2 años</option>
                                    <option value="3">3 años</option>
                                    <option value="4">4 años</option>
                                    <option value="5">5 años</option>
                                    <option value="6">6 años</option>
                                    <option value="7">7 años</option>
                                    <option value="8">8 años</option>
                                    <option value="9">9 años</option>
                                    <option value="10">10 años</option>
                                    <option value="11">11 años</option>
                                    <option value="12">12 años</option>
                                    <option value="13">13 años</option>
                                    <option value="14">14 años</option>
                                    <option value="15">15 años</option>
                                    <option value="16">16 años</option>
                                    <option value="17">17 años</option>
                                    <option value="18">18 años</option>
                                    <option value="19">19 años</option>
                                    <option value="20">20 años</option>
                                </select>
                                <small className="text-danger">{formErrors['convenio[vigencia_text]']}</small>
                            </div>
                            <div className="col-md-6">
                                <label id="labelfecvigen1" htmlFor="selectField">Fecha de Vigencia de Convenio</label>
                                <input type="date" id="convenio_fecha_vence_convenio"
                                       readOnly
                                       name="appbundle_convenio_admin[fecha_vence_convenio]" className="form-control mb-2"/>
                                <small className="text-danger">{formErrors['convenio[fecha_vence_convenio]']}</small>
                            </div>
                        </div>
                        <h3 className="text-center" style={{marginBottom: '2rem'}}><strong>FIRMANTES</strong></h3>
                        <div className="row">
                            <div className="col-md-6">
                                <p><strong className="text-secondary">Por el IMSS</strong></p>
                                <label id="jobSignImssAdminUno" htmlFor="selectField"><span
                                    className={'text-danger'}>*</span> Cargo
                                    Firmante IMSS 1</label>
                                <select className="form-control mb-2" id="convenio_cargoFirmaIdFirst"
                                        name="appbundle_convenio_admin[cargoFirmaIdFirst]" required>
                                    <option value="">Selecciona Cargo del Firmante</option>
                                    {cargos.map((item, index) => (
                                        <option value={item.id} key={index}>{item.nombre}</option>))}
                                </select>
                                <small className="text-danger">{formErrors['convenio[cargoFirmaIdFirst]']}</small>
                            </div>
                            <div className="col-md-6">
                                <p><strong className="text-secondary">Por la Institución Educativa</strong></p>
                                <label id="jobSignInstAdminUno" htmlFor="selectField"><span
                                    className={'text-danger'}>*</span>
                                    Cargo del Firmante 1 de la Institución
                                </label>
                                <input type="text" className="form-control mb-2" id="convenio_cargo_emite_signfirst"
                                       name="appbundle_convenio_admin[cargo_emite_signfirst]" required/>
                                <small className="text-danger">{formErrors['convenio[cargo_emite_signfirst]']}</small>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-6">
                                <label id="labelimss1" htmlFor="selectField"><span
                                    className={'text-danger'}>*</span> Nombre del
                                    Firmante IMSS 1</label>
                                <input id="convenio_recibe_signfirst"
                                       name="appbundle_convenio_admin[recibe_signfirst]" type="text" className="form-control mb-2"
                                       required/>
                                <small className="text-danger">{formErrors['convenio[recibe_signfirst]']}</small>
                            </div>
                            <div className="col-md-6">
                                <label id="labelfirmante1" htmlFor="selectField"><span
                                    className={'text-danger'}>*</span>
                                    Nombre del Firmante 1 de la Institución
                                </label>
                                <input id="convenio_nombre_emite_signfirst"
                                       name="appbundle_convenio_admin[nombre_emite_signfirst]" type="text"
                                       className="form-control mb-2" required/>
                                <small
                                    className="text-danger">{formErrors['convenio[nombre_emite_signfirst]']}</small>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-6">
                                <label id="labelimss2" htmlFor="selectField"><span
                                    className={'text-danger'}></span> Cargo Firmante
                                    IMSS 2</label>
                                <select className="form-control mb-2" id="convenio_cargoFirmaIdSecond"
                                        name="appbundle_convenio_admin[cargoFirmaIdSecond]">
                                    <option value="">Selecciona Cargo del Firmante</option>
                                    {cargos.map((item, index) => (
                                        <option value={item.id} key={index}>{item.nombre}</option>))}
                                </select>
                                <small
                                    className="text-danger">{formErrors['convenio[cargoFirmaIdSecond]']}</small>
                            </div>
                            <div className="col-md-6">
                                <label id="labelfirmante2" htmlFor="selectField"><span
                                    className={'text-danger'}></span>
                                    Cargo del Firmante 2 de la Institución
                                </label>
                                <input id="convenio_cargo_emite_signsecond"
                                       name="appbundle_convenio_admin[cargo_emite_signsecond]" type="text"
                                       className="form-control mb-2" />
                                <small
                                    className="text-danger">{formErrors['convenio[cargo_emite_signsecond]']}</small>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-6">
                                <label id="labelfirmaimss2" htmlFor="selectField"><span
                                    className={'text-danger'}></span> Nombre del
                                    Firmante IMSS 2</label>
                                <input id="convenio_recibe_signsecond"
                                       name="appbundle_convenio_admin[recibe_signsecond]" type="text" className="form-control mb-2"
                                       />
                                <small
                                    className="text-danger">{formErrors['convenio[recibe_signsecond]']}</small>
                            </div>
                            <div className="col-md-6">
                                <label id="labelnombrefir2" htmlFor="selectField"><span
                                    className={'text-danger'}></span>
                                    Nombre del Firmante 2 de la Institución
                                </label>
                                <input id="convenio_nombre_emite_signsecond"
                                       name="appbundle_convenio_admin[nombre_emite_signsecond]" type="text"
                                       className="form-control mb-2" />
                                <small
                                    className="text-danger">{formErrors['convenio[nombre_emite_signsecond]']}</small>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-12">
                                <label htmlFor="selectField">Agregar URL de documentos del convenio:</label>
                                <input
                                    placeholder="https://www.imss.gob o //host/file"
                                    id="url_convenio"
                                    name="appbundle_convenio_admin[url_convenio]"
                                    type="text"
                                    className="form-control mb-2"
                                    pattern="((https?|ftp)?:)?\/\/.+"
                                    title="Por favor ingrese una URL válida que comience con http:// o https:// o // o ftp://"
                                />
                                <small
                                    className="text-danger">{formErrors['convenio[url_convenio]']}</small>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-6">
                                <label htmlFor="convenio_buscar">&nbsp;</label>
                                <button
                                    type={'submit'}
                                    className="btn btn-primary btn-block">Guardar
                                </button>
                            </div>
                        </div>
                    </>
                }
            </div>
        </form>
    </>)
}
