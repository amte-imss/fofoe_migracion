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
																			 tipo=''
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
		<FormValidator errors={formErrors} setErrors={setFormErrors}>
			<form onSubmit={handleSubmit} ref={formRef} noValidate autoComplete={'off'}>
				<div className="container">
					<div className="row">
						<div className="col-md-6">
							<label htmlFor="rfc-input" className="text-success"><span className={'text-danger'}>*</span> R.F.C. de
								Convenio</label>
							<input id="appbundle_convenio_admin_rfc" name="appbundle_convenio_admin[rfc]" type="text"
										 ref={rfcRef}
										 maxLength={13}
										 minLength={12}
										 className="form-control" placeholder="Escribe una R.F.C." required/>
							<small className="text-danger">{formErrors['appbundle_convenio_admin[rfc]']}</small>
						</div>
						<div className="col-md-6">
							<label htmlFor="appbundle_convenio_admin_buscar">&nbsp;</label>
							<button id="appbundle_convenio_admin_buscar"
											disabled={!canSearchByRFC()}
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
									<label id="labelrazonsocial" htmlFor="selectField"><span
										className={'text-danger'}>*</span> Razón Social</label>
									<input id="appbundle_convenio_admin_razon_social"
												 required={true}
												 maxLength={250}
												 minLength={5}
												 name="appbundle_convenio_admin[razon_social]" type="text" className="form-control mb-2"/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[razon_social]']}</small>
								</div>
							</div>
							<div className="row">
								<div className="col-md-6">
									<label id="nombreConvenioAdminLbl" htmlFor="selectField"><span
										className={'text-danger'}>*</span> Nombre Comercial</label>
									<input id="appbundle_convenio_admin_nombre"
												 required
												 minLength={5}
												 maxLength={255}
												 name="appbundle_convenio_admin[nombre]" type="text"
												 className="form-control mb-2"/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[nombre]']}</small>
								</div>
								<div className="col-md-6">
									<label id="conveniolabel" htmlFor="selectField"><span
										className={'text-danger'}>*</span> Institución</label>
									<select className="form-control mb-2"
													required={true}
													onChange={handleChandeInstitucion}
													id="appbundle_convenio_admin_institucion"
													name="appbundle_convenio_admin[institucion]">
										<option value="">Selecciona una opción</option>
										{instituciones.map((item, index) => (<option value={item.id} key={index}>{item.nombre}</option>))}
									</select>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[institucion]']}</small>
								</div>
							</div>
							<div className="row">
								<div className="col-md-6">
									<label id="labeltipo" htmlFor="selectField"><span
										className={'text-danger'}>*</span> Tipo</label>
									<select className="form-control mb-2" id="appbundle_convenio_admin[tipo]"
													required
													onChange={handleChangeTipoConvenio}
													name="appbundle_convenio_admin[tipo]">
										<option value="">Selecciona Tipo de Convenio</option>
										{!isCame &&
											<option value="General">General</option>}
										<option value="Específico">Específico</option>
									</select>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[tipo]']}</small>
								</div>
								{showConveniosGenerales &&
									<div className="col-md-6">
										<label id="convenioGralId" htmlFor="selectField"><span
											className={'text-danger'}>*</span> Convenio General</label>
										<select className="form-control mb-2" id="appbundle_convenio_admin[convenio_general]"
														name="appbundle_convenio_admin[convenio_general]" required={tipo === 'Específico'}>
											<option value="">Selecciona el Convenio General</option>
											{conveniosGenerales.map((item, index) => (
												<option value={item.id} key={index}>{item.nombre}</option>))}
										</select>
										<small className="text-danger">{formErrors['appbundle_convenio_admin[convenio_general]']}</small>
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
											<Select id="appbundle_convenio_admin_delegacion" name="appbundle_convenio_admin[delegacion]"
															closeMenuOnSelect={false}
															required={true}
															components={animatedComponents} isMulti
															options={ooadsOptions()}
															value={selectedOoads}
															placeholder="Selecciona una OOAD o varias"
															onChange={(selectedOption) => {
																handleChangeMultiSelect('appbundle_convenio_admin[delegacion]',
																	setSelectedOoads,
																	selectedOption)
															}}
											></Select>
											<small className="text-danger">{formErrors['appbundle_convenio_admin[delegacion]']}</small>
										</div>}
								</div>
							}
							{isCame &&
								<div className="row">
									<div className="col-md-6">
										<label htmlFor="ooad">OOAD</label>
										<select id="appbundle_convenio_admin_delegacion"
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
									<small className="text-danger">{formErrors['appbundle_convenio_admin[sector]']}</small>
								</div>
							</div>
							<div className="row">
								<div className="col-md-12">
									<label id="objAdminLblId" htmlFor="textareaField"><span className={'text-danger'}>*</span> Objetivo de
										Colaboración</label>
									<textarea id="appbundle_convenio_admin_objetivo_colaboracion"
														name="appbundle_convenio_admin[objetivo_colaboracion]"
														className="form-control mb-2"
														placeholder="Escribe aquí" required></textarea>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[objetivo_colaboracion]']}</small>
								</div>
							</div>
							<div className="row">
								{false && <div className="col-md-6" onInput={handleChangeTodosCiclosAcademicos}>
									<label>¿Para todos los ciclos académicos?</label>
									<div className="row">
										<div className="form-check col-md-2" style={{marginLeft: '40px'}}>
											<input type="radio" className="form-check-input"
														 value={1}
														 id="todosCiclosAcademicosSi" name="todosCiclosAcademicos"
											/>
											<label className="form-check-label" htmlFor="todosCiclosAcademicosSi">Sí</label>
										</div>
										<div className="form-check col-md-2" style={{marginLeft: '40px'}}>
											<input type="radio" className="form-check-input"
														 value={0}
														 id="todosCiclosAcademicosNo" name="todosCiclosAcademicos"
											/>
											<label className="form-check-label" htmlFor="todosCiclosAcademicosNo">No</label>
										</div>
									</div>
								</div>}
								{showSelectCiclosAcademicos &&
									<div className="col-md-6">
										<label id="motivoAdminLblId" htmlFor="selectField"> Campos Clínicos</label>
										<Select id="appbundle_convenio_admin_cicloAcademico" name="appbundle_convenio_admin[cicloAcademico]"
														closeMenuOnSelect={false}
														value={selectedCiclosAcademicos}
														components={animatedComponents} isMulti options={optionsMotivos()}
														placeholder="Selecciona un ciclo académico o varios"
														onChange={selectedOption => {
															handleChangeMultiSelect('appbundle_convenio_admin[cicloAcademico]',
																setSelectedCiclosAcademicos,
																selectedOption)
														}}
										></Select>
										<small className="text-danger">{formErrors['appbundle_convenio_admin[cicloAcademico]']}</small>
									</div>
								}
							</div>
							<div className="row">

								<div className="col-md-6">
									<label id="appbundle_convenio_nivelAcademicoL" htmlFor="selectField">Nivel o grado Académico</label>
									<select className="form-control mb-2"
													id="appbundle_convenio_admin_nivelAcademico"
													onChange={handleChangeNivelAcademico}
													name="appbundle_convenio_admin[nivelAcademico]">
										<option value="">Selecciona Nivel o grado Académico</option>
										{
											gradosAcademicos.map((item, index) => (
												<option value={item.id} key={index}>{item.nombre}</option>))
										}
										<option value="other">Otro</option>
									</select>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[nivelAcademico]']}</small>
								</div>
							</div>
							<div className="row">
								<div className="col-md-6">
									<label id="disciplinaAdminLblId" htmlFor="selectField">{tipo !== 'General' && <span className={'text-danger'}>*</span>} Disciplina</label>
									<select className="form-control mb-2"
													onInput={handleChangeDisciplina}
													id="appbundle_convenio_admin_disciplina"
													name="appbundle_convenio_admin[disciplina]" required={tipo !== 'General'}>
										<option value="">Selecciona Disciplina</option>
										{disciplinas.map((item, index) => (<option key={index} value={item.id}>{item.nombre}</option>))}
										{!isCame && <option value="other">Otra</option>}
									</select>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[disciplina]']}</small>
								</div>
								<div className="col-md-6">
									<label id="carreraAdminLblId" htmlFor="selectField">{tipo !== 'General' && <span className={'text-danger'}>*</span>} Carrera</label>
									<select className="form-control mb-2" id="appbundle_convenio_admin_carrera"
													onInput={handleChangeCarrera}
													name="appbundle_convenio_admin[carrera]" required={tipo !== 'General'}>
										<option value="">Selecciona Carrera</option>
										{carreras.map((item, index) => (<option key={index} value={item.id}>{item.nombre}</option>))}
										{!isCame && <option value="other">Otra</option>}
									</select>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[carrera]']}</small>
								</div>
							</div>
							<div className="row">
								<div className="col-md-6">
									<label id="nomProAdminLblId" htmlFor="selectField">{tipo !== 'General' && <span className={'text-danger'}>*</span>} Nombre del Programa</label>
									<input type="text" className="form-control mb-2" id="appbundle_convenio_admin_nombre_programa"
												 name="appbundle_convenio_admin[nombre_programa]" required={tipo !== 'General'}/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[nombre_programa]']}</small>
								</div>
								<div className="col-md-6">
									<label id="fechaFirmaAdminLblId" htmlFor="selectField"><span className={'text-danger'}>*</span> Fecha
										de Firma</label>
									<input type="date" id="appbundle_convenio_admin_fecha_firma"
												 max={useMaxDateAllowed ? maxDateAllowed : null}
												 name="appbundle_convenio_admin[fecha_firma]" className="form-control mb-2" required/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[fecha_firma]']}</small>
								</div>
							</div>
							<div className="row">
								<div className="col-md-6">
									<label id="vigenciaAnios" htmlFor="selectField"><span className={'text-danger'}>*</span> Vigencia
										(Años)</label>
									<select className="form-control mb-2"
													onInput={handleChangeVigenciaText}
													id="appbundle_convenio_admin_vigencia_text"
													name="appbundle_convenio_admin[vigencia_text]" required>
										<option value="">Selecciona Vigencia</option>
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
									<small className="text-danger">{formErrors['appbundle_convenio_admin[vigencia_text]']}</small>
								</div>
								<div className="col-md-6">
									<label id="labelfecvigen1" htmlFor="selectField">Fecha de Vigencia de Convenio</label>
									<input type="date" id="appbundle_convenio_admin_fecha_vence_convenio"
												 readOnly
												 name="appbundle_convenio_admin[fecha_vence_convenio]" className="form-control mb-2"/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[fecha_vence_convenio]']}</small>
								</div>
							</div>
							<div className="row">
								<div className="col-md-6">
									<label id="labelfechaota" htmlFor="selectField">Fecha de Emisión OTA</label>
									<input type="date" id="appbundle_convenio_admin_fecha_ota"
												 max={useMaxDateAllowed ? maxDateAllowed : null}
												 name="appbundle_convenio_admin[fecha_ota]"
												 className="form-control mb-2"/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[fecha_ota]']}</small>
								</div>
								<div className="col-md-6">
									<label id="labelota2" htmlFor="selectField">Fecha de Vigencia de Emisión OTA</label>
									<input type="date" id="appbundle_convenio_admin_fecha_vence_ota"
												 min={useMaxDateAllowed ? maxDateAllowed : null}
												 name="appbundle_convenio_admin[fecha_vence_ota]" className="form-control mb-2"/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[fecha_vence_ota]']}</small>
								</div>
							</div>
							<div className="row">
								<div className="col-md-6">
									<label id="labelrvoe" htmlFor="selectField">Fecha de Emisión RVOE</label>
									<input type="date" id="appbundle_convenio_admin_fecha_rvoe"
												 max={useMaxDateAllowed ? maxDateAllowed : null}
												 name="appbundle_convenio_admin[fecha_rvoe]" className="form-control mb-2"/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[fecha_rvoe]']}</small>
								</div>
								<div className="col-md-6">
									<label id="labelrvoe2" htmlFor="selectField">Fecha de Vigencia de Emisión RVOE</label>
									<input type="date" id="appbundle_convenio_admin_fecha_vence_rvoe"
												 min={useMaxDateAllowed ? maxDateAllowed : null}
												 name="appbundle_convenio_admin[fecha_vence_rvoe]" className="form-control mb-2"/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[fecha_vence_rvoe]']}</small>
								</div>
							</div>
							<div className="row">
								<div className="col-md-12">
									<label>¿Cuentas con una Carta Compromiso?</label>
								</div>
								<div className="form-check col-md-2" style={{marginLeft: '40px'}}>
									<input type="radio" className="form-check-input"
												 value={1}
												 onInput={handleChangeCartaIntencion}
												 id="cartaIntencionSi" name="cartaIntencion"
									/>
									<label className="form-check-label" htmlFor="cartaIntencionSi">Sí</label>
								</div>
								<div className="form-check col-md-2">
									<input type="radio" className="form-check-input"
												 value={0}
												 onInput={handleChangeCartaIntencion}
												 id="cartaIntencionNo" name="cartaIntencion"
									/>
									<label className="form-check-label" htmlFor="cartaIntencionNo">No</label>
								</div>
							</div>
							{showCOMAEM && <div className="row"
																	id="contenedor_fechas_comaen">
								<div className="col-md-6">
									<label id="labelcomaem" htmlFor="selectField">Fecha de Emisión de Acreditación (COMAEM u
										otros)</label>
									<input type="date" id="appbundle_convenio_admin_fecha_emite_comaem"
												 max={useMaxDateAllowed ? maxDateAllowed : null}
												 name="appbundle_convenio_admin[fecha_emite_comaem]" className="form-control mb-2"/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[fecha_emite_comaem]']}</small>
								</div>
								<div className="col-md-6">
									<label id="labelcomaem2" htmlFor="selectField">Fecha de Vigencia de Emisión de Acreditación (COMAEM u
										otros)</label>
									<input type="date" id="appbundle_convenio_admin_fecha_vence_comaem"
												 min={useMaxDateAllowed ? maxDateAllowed : null}
												 name="appbundle_convenio_admin[fecha_vence_comaem]" className="form-control mb-2"/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[fecha_vence_comaem]']}</small>
								</div>
							</div>}
							{!showCOMAEM && <div className="row"
																	 id="contenedor_fechas_comaen">
								<div className="col-md-6">
									<label id="labelcomaem" htmlFor="selectField">Fecha de Carta de Intención</label>
									<input type="date" id="appbundle_convenio_admin_fecha_carta_intencion"
												 name="appbundle_convenio_admin[fechaCartaIntencion]" className="form-control mb-2"/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[fechaCartaIntencion]']}</small>
								</div>

							</div>}
							<h3 className="text-center" style={{marginBottom: '2rem'}}><strong>FIRMANTES</strong></h3>
							<div className="row">
								<div className="col-md-6">
									<p><strong className="text-secondary">Por el IMSS</strong></p>
									<label id="jobSignImssAdminUno" htmlFor="selectField"><span className={'text-danger'}>*</span> Cargo
										Firmante IMSS 1</label>
									<select className="form-control mb-2" id="appbundle_convenio_admin_cargoFirmaIdFirst"
													name="appbundle_convenio_admin[cargoFirmaIdFirst]" required>
										<option value="">Selecciona Cargo del Firmante</option>
										{cargos.map((item, index) => (<option value={item.id} key={index}>{item.nombre}</option>))}
									</select>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[cargoFirmaIdFirst]']}</small>
								</div>
								<div className="col-md-6">
									<p><strong className="text-secondary">Por la Institución Educativa</strong></p>
									<label id="jobSignInstAdminUno" htmlFor="selectField"><span className={'text-danger'}>*</span>
										Cargo del Firmante 1 de la Institución
									</label>
									<input type="text" className="form-control mb-2" id="appbundle_convenio_admin_cargo_emite_signfirst"
												 name="appbundle_convenio_admin[cargo_emite_signfirst]" required/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[cargo_emite_signfirst]']}</small>
								</div>
							</div>
							<div className="row">
								<div className="col-md-6">
									<label id="labelimss1" htmlFor="selectField"><span className={'text-danger'}>*</span> Nombre del
										Firmante IMSS 1</label>
									<input id="appbundle_convenio_admin_recibe_signfirst"
												 name="appbundle_convenio_admin[recibe_signfirst]" type="text" className="form-control mb-2"
												 required/>
									<small className="text-danger">{formErrors['appbundle_convenio_admin[recibe_signfirst]']}</small>
								</div>
								<div className="col-md-6">
									<label id="labelfirmante1" htmlFor="selectField"><span className={'text-danger'}>*</span>
										Nombre del Firmante 1 de la Institución
									</label>
									<input id="appbundle_convenio_admin_nombre_emite_signfirst"
												 name="appbundle_convenio_admin[nombre_emite_signfirst]" type="text"
												 className="form-control mb-2" required/>
									<small
										className="text-danger">{formErrors['appbundle_convenio_admin[nombre_emite_signfirst]']}</small>
								</div>
							</div>
							<div className="row">
								<div className="col-md-6">
									<label id="labelimss2" htmlFor="selectField"><span className={'text-danger'}>*</span> Cargo Firmante
										IMSS 2</label>
									<select className="form-control mb-2" id="appbundle_convenio_admin_cargoFirmaIdSecond"
													name="appbundle_convenio_admin[cargoFirmaIdSecond]" required>
										<option value="">Selecciona Cargo del Firmante</option>
										{cargos.map((item, index) => (<option value={item.id} key={index}>{item.nombre}</option>))}
									</select>
									<small
										className="text-danger">{formErrors['appbundle_convenio_admin[cargoFirmaIdSecond]']}</small>
								</div>
								<div className="col-md-6">
									<label id="labelfirmante2" htmlFor="selectField"><span className={'text-danger'}>*</span>
										Cargo del Firmante 2 de la Institución
									</label>
									<input id="appbundle_convenio_admin_cargo_emite_signsecond"
												 name="appbundle_convenio_admin[cargo_emite_signsecond]" type="text"
												 className="form-control mb-2" required/>
									<small
										className="text-danger">{formErrors['appbundle_convenio_admin[cargo_emite_signsecond]']}</small>
								</div>
							</div>
							<div className="row">
								<div className="col-md-6">
									<label id="labelfirmaimss2" htmlFor="selectField"><span className={'text-danger'}>*</span> Nombre del
										Firmante IMSS 2</label>
									<input id="appbundle_convenio_admin_recibe_signsecond"
												 name="appbundle_convenio_admin[recibe_signsecond]" type="text" className="form-control mb-2"
												 required/>
									<small
										className="text-danger">{formErrors['appbundle_convenio_admin[recibe_signsecond]']}</small>
								</div>
								<div className="col-md-6">
									<label id="labelnombrefir2" htmlFor="selectField"><span className={'text-danger'}>*</span>
										Nombre del Firmante 2 de la Institución
									</label>
									<input id="appbundle_convenio_admin_nombre_emite_signsecond"
												 name="appbundle_convenio_admin[nombre_emite_signsecond]" type="text"
												 className="form-control mb-2" required/>
									<small
										className="text-danger">{formErrors['appbundle_convenio_admin[nombre_emite_signsecond]']}</small>
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
										className="text-danger">{formErrors['appbundle_convenio_admin[url_convenio]']}</small>
								</div>
							</div>
							<div className="row">
								<div className="col-md-6">
									<label htmlFor="appbundle_convenio_admin_buscar">&nbsp;</label>
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
		</FormValidator>
	</>)
}
