import creactRoot from 'react-dom/client';
import React from "react";
import {FormValidator} from "../../../components/FormValidator/FormValidator";
import InputAutoComplete from "../../../components/InputAutoComplete/InputAutoComplete";
import {getGradosAcademicos} from "../../utils";
import {unidadesByDelegacionGet} from "../../../api/unidad";
import Select from 'react-select'
import {getSchemeAndHttpHost, toSelectArray} from "../../../utils";
import Loader from "../../../components/Loader/Loader";
import {storeResidenciaNoIMSS} from "../../Api";
import Swal from 'sweetalert2'
import 'sweetalert2/src/sweetalert2.scss'

const FormResidente = ({paises, especialidades, ooads, ciclos_academicos}) => {

	const [isLoading, setIsLoading] = React.useState(false)
	const [errors, setErrors] = React.useState({});

	const [especialidad, setEspecialidad] = React.useState(null);
	const [nacionalidad, setNacionalidad] = React.useState(null);
	const [ooad, setOoad] = React.useState(null);
	const [sede, setSede] = React.useState(null);
	const [ooad2, setOoad2] = React.useState(null);
	const [subSede, setSubSede] = React.useState(null);

	const [unidadesSede, setUnidadesSede] = React.useState([]);
	const [unidadesSubsede, setUnidadesSubSede] = React.useState([]);

	const handleSetOOAD = (ooad, tipo) => {
		setIsLoading(true)
		unidadesByDelegacionGet(ooad.value).then(({data}) => {
			if(tipo === 'sede'){
				setOoad(ooad);
				setUnidadesSede(data);
			}else{
				setOoad2(ooad);
				setUnidadesSubSede(data);
			}
		}).finally(() => {
			setIsLoading(false);
		})
	}

	const handleEspecialidad = (value) => {
		const found = especialidades.find(item => {
			return item.id.toString() === value.value.toString();
		})
		setEspecialidad(found);
	}

	const handleSubmit = (event) => {
		event.preventDefault();
		setIsLoading(true)
		const formData = new FormData(event.target);
		formData.append('residente_extranjero_no_imss[residente][nacionalidad]', nacionalidad.label);
		formData.append('residente_extranjero_no_imss[especialidad]', especialidad.nombre);
		formData.append('residente_extranjero_no_imss[delegacion]', ooad.label);
	//	formData.append('residente_extranjero_no_imss[subdelegacion]', ooad2.label);
		formData.append('residente_extranjero_no_imss[sede]', sede.label);
		formData.append('residente_extranjero_no_imss[subsede]', subSede.label);
		storeResidenciaNoIMSS(formData).then(json => {
			console.log(json);
			if(json.status){
				Swal.fire(
					json.message,
					'',
					'success',
				).then(() => {
					window.location.href = getSchemeAndHttpHost(`/posgrado/residentes/no_imss`);
				})
			}
		}).catch(error => {
			console.error(error);
		}).finally(() => {
			setIsLoading(false)
		});
	}

	return (
		<>
			<Loader show={isLoading}/>
			<FormValidator errors={errors} setErrors={setErrors}>
				<form onSubmit={handleSubmit}>
					<fieldset className="fieldset">
						<legend>Datos Generales</legend>
						<div className="row">
							<div className="col-md-4">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['nombre'] ? 'has-error' : ''}`}>
									<label htmlFor="input-name">Nombre(s) <span className="text-danger">*</span></label>
									<input type="text" className="form-control" id="input-name"
												 required
												 name={'residente_extranjero_no_imss[residente][usuario][nombre]'}
												 placeholder="Nombres" />
									{errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['nombre'] ? <p className="help-block">{errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['nombre']}</p> : null }
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['apellidoPaterno'] ? 'has-error': ''}`}>
									<label htmlFor="input-last_name">Apellido Paterno <span className="text-danger">*</span></label>
									<input type="text" className="form-control"
												 required
												 name={'residente_extranjero_no_imss[residente][usuario][apellidoPaterno]'}
												 id="input-last_name" placeholder="Apellido Paterno" />
									{errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['apellidoPaterno'] ? <p className="help-block">{errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['apellidoPaterno']}</p> : null}
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['apellidoMaterno'] ? 'has-error' : ''}`}>
									<label htmlFor="input-last_name">Apellido Materno</label>
									<input type="text" className="form-control"
												 name={'residente_extranjero_no_imss[residente][usuario][apellidoMaterno]'}
												 id="input-other_name" placeholder="Apellido Materno" />
									{errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['apellidoMaterno'] ? <p className="help-block">{errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['apellidoMaterno']}</p> : null }
								</div>
							</div>
						</div>
						<div className="row">
							<div className="col-md-4">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['curp'] ? 'has-error': ''}`}>
									<label htmlFor="input-curp">CURP </label>
									<input type="text" className="form-control"
										   maxLength={18}
												 required name={'residente_extranjero_no_imss[residente][usuario][curp]'}
												 id="input-curp" placeholder="CURP" />
									{errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['curp'] ? <p className="help-block">{errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['curp']}</p> : null}
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['correo'] ? 'has-error' : ''}`}>
									<label htmlFor="input-email">Correo Electrónico <span className="text-danger">*</span></label>
									<input type="email" className="form-control"
												 required name={'residente_extranjero_no_imss[residente][usuario][correo]'}
												 id="input-email" placeholder="juan@mail.com" />
									{errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['correo'] ? <p className="help-block">{errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['correo']}</p> : null }
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['telefono'] ? 'has-error' : ''}`}>
									<label htmlFor="input-phone">Teléfono</label>
									<input type="text" className="form-control"
												 name={'residente_extranjero_no_imss[residente][usuario][telefono]'}
												 id="input-phone" placeholder="55-5555-5555" />
									{errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['telefono'] ? <p className="help-block">{errors['residente_extranjero_no_imss']?.['residente']?.['usuario']?.['telefono']}</p> : null }
								</div>
							</div>
						</div>
						<div className="row">
							<div className="col-md-4">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['residente']?.['pasaporte'] ? 'has-error': ''}`}>
									<label htmlFor="input-pasaporte">Pasaporte <span className="text-danger">*</span></label>
									<input type="text" className="form-control"
												 required name={'residente_extranjero_no_imss[residente][pasaporte]'}
												 id="input-pasaporte" placeholder="# Pasaporte" />
									{errors['residente_extranjero_no_imss']?.['residente']?.['pasaporte'] ? <p className="help-block">{errors['residente_extranjero_no_imss']?.['residente']?.['pasaporte']}</p> : null}
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['residente']?.['nacionalidad'] ? 'has-error' : ''}`}>
									<label htmlFor="input-nacionalidad">Nacionalidad <span className="text-danger">*</span></label>
									<Select
										required
										onChange={setNacionalidad}
										className={'form-group-die'}
										placeholder={'Nacionalidad'}
										inputId={'input-nacionalidad'}
										options={toSelectArray(paises, {value:'id', name:'nombre'})}
									/>
									{errors['residente_extranjero_no_imss']?.['residente']?.['nacionalidad'] ? <p className="help-block">{errors['residente_extranjero_no_imss']?.['residente']?.['nacionalidad']}</p> : null }
									{false && <input type="text" className="form-control"
												 required
												 name={'nacionalidad'}
												 id="input-nacionalidad" placeholder="Nacionalidad" />}
								</div>
							</div>
						</div>
					</fieldset>
					<fieldset className="fieldset">
						<legend>Datos Solicitud</legend>
						<div className="row">
							<div className="col-md-4">
								<div className={`form-group ${errors.solicitud ? 'has-error' : ''}`}>
									<label htmlFor="input-solicitud">Tipo de solicitud <span className="text-danger">*</span></label>
									<input type="text" className="form-control"
												 required
												 readOnly
												 value={'Rotación parcial'}
												 name={'solicitud'}
												 id="input-solicitud" placeholder="Tipo de solicitud" />
									{errors.solicitud ? <p className="help-block">{errors.solicitud}</p> : null }
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['especialidad'] ? 'has-error' :'' }`}>
									<label htmlFor="input-especialidad">Especialidad <span className="text-danger">*</span></label>
									{false && <input type="text" className="form-control"
												 required
												 name={'especialidad'}
												 id="input-especialidad" placeholder="Especialidad" /> }
									<Select
										required
										onChange={handleEspecialidad}
										className={'form-group-die'}
										placeholder={'Especialidad'}
										inputId={'input-especialidad'}
										options={toSelectArray(especialidades, {value:'id', name:'nombre'})}
									/>
									{errors['residente_extranjero_no_imss']?.['especialidad'] ? <p className="help-block">{errors['residente_extranjero_no_imss']?.['especialidad']}</p> : null}
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['grado'] ? 'has-error': ''}`}>
									<label htmlFor="input-grado_academico">Grado Académico <span className="text-danger">*</span></label>
									{false && <input type="text" className="form-control"
												 required
												 name={'residente_extranjero_no_imss[grado]'}
												 id="input-grado_academico" placeholder="Grado Académicos" /> }
									<select name="residente_extranjero_no_imss[grado]" className="form-control" id="input-grado_academico" required>
										<option value="">Seleccionar...</option>
										{especialidad ? getGradosAcademicos(especialidad.duracion).map((item, index) => {
											return <option
												key={`especialidad_option_${index}`}
												value={index+1}>{item}</option>
										}) : null}
									</select>
									{errors['residente_extranjero_no_imss']?.['grado'] ? <p className="help-block">{errors['residente_extranjero_no_imss']?.['grado']}</p> : null}
								</div>
							</div>
						</div>
						<div className="row">
							<div className="col-md-3">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['fecha_inicio'] ? 'has-error': ''}`}>
									<label htmlFor="input-solicitud">Inicio <span className="text-danger">*</span></label>
									<input type="date" className="form-control"
												 required
												 name={'residente_extranjero_no_imss[fecha_inicio]'}
												 id="input-fecha_inicio" />
									{errors['residente_extranjero_no_imss']?.['fecha_inicio'] ?	<p className="help-block">{errors['residente_extranjero_no_imss']?.['fecha_inicio']}</p> : null}
								</div>
							</div>
							<div className="col-md-3">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['fecha_termino'] ? 'has-error' : ''}`}>
									<label htmlFor="input-termino">Termino <span className="text-danger">*</span></label>
									<input type="date" className="form-control"
												 required
												 name={'residente_extranjero_no_imss[fecha_termino]'}
												 id="input-fecha_termino" />
									{errors['residente_extranjero_no_imss']?.['fecha_termino'] ? <p className="help-block">{errors['residente_extranjero_no_imss']?.['fecha_termino']}</p> : null }
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['ciclo'] ? 'has-error' : ''}`}>
									<label htmlFor="input-ciclo_academico">Ciclo Académico</label>
									<select name="residente_extranjero_no_imss[ciclo]" required
													className={'form-control'} id="input-ciclo_academico">
										<option value="">Seleccionar</option>
										{ciclos_academicos.map((item, index) => {
											return <option key={`ciclo_academico_${index}`} value={item}>{item}</option>
										})}
									</select>
									{errors['residente_extranjero_no_imss']?.['ciclo'] ? 	<p className="help-block">{errors['residente_extranjero_no_imss']?.['ciclo']}</p> : null }
								</div>
							</div>
						</div>
						<div className="row">
							<div className="col-md-4">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['folio'] ? 'has-error' : ''}`}>
									<label htmlFor="input-folio_imss">Número de Oficio  <span className="text-danger">*</span></label>
									<input type="text" className="form-control"
												 required
												 name={'residente_extranjero_no_imss[folio]'}
												 id="input-folio_imss" />
									{errors['residente_extranjero_no_imss']?.['folio'] ? 	<p className="help-block">{errors['residente_extranjero_no_imss']?.['folio']}</p> : null }
								</div>
							</div>
						</div>
						<div className="row">
							<div className="col-md-6">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['delegacion'] ? 'has-error' : ''}`}>
									<label htmlFor="input-ooad">OOAD <span className="text-danger">*</span></label>
									{false && <input type="text" className="form-control"
												 required
												 name={'ooad'}
												 id="input-ooad" placeholder="OOAD" /> }
									<Select
										required
										onChange={(value) => handleSetOOAD(value, 'sede')}
										className={'form-group-die'}
										placeholder={'OOAD Sede'}
										inputId={'input-ooad'}
										options={toSelectArray(ooads, {value:'id', name:'nombre'})}
									/>
									{errors['residente_extranjero_no_imss']?.['delegacion'] ? 	<p className="help-block">{errors['residente_extranjero_no_imss']?.['delegacion']}</p> : null }
								</div>
							</div>
							<div className="col-md-6">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['sede'] ? 'has-error' : ''}`}>
									<label htmlFor="input-unidad_sede">Unidad/UMAE Sede <span className="text-danger">*</span></label>
									{false && <input type="text" className="form-control"
												 required
												 name={'unidad_sede'}
												 id="input-unidad_sede" placeholder="Unidad Sede" /> }
									<Select
										required
										onChange={setSede}
										className={'form-group-die'}
										placeholder={'Unidad Sede'}
										inputId={'input-unidad-sede'}
										options={toSelectArray(unidadesSede, {value:'id', name:'nombre'})}
									/>
									{errors['residente_extranjero_no_imss']?.['sede'] ? 	<p className="help-block">{errors['residente_extranjero_no_imss']?.['sede']}</p> :  null }
								</div>
							</div>
						</div>
						<div className="row">
							<div className="col-md-6">
								<div className={`form-group ${errors['residente_extranjero_no_imss']?.['subdelegacion'] ? 'has-error' : ''}`}>
									<label htmlFor="input-ooad">OOAD SubSede<span className="text-danger">*</span></label>
									<Select
										required
										onChange={(value) => handleSetOOAD(value, 'subsede')}
										className={'form-group-die'}
										placeholder={'OOAD SubSede'}
										inputId={'input-ooad2'}
										options={toSelectArray(ooads, {value:'id', name:'nombre'})}
									/>
									{errors['residente_extranjero_no_imss']?.['subdelegacion'] ? 	<p className="help-block">{errors['residente_extranjero_no_imss']?.['subdelegacion']}</p> : null }
								</div>
							</div>
							<div className="col-md-6">
								<div className={`form-group ${errors.unidad_subsede ? 'has-error' : ''}`}>
									<label htmlFor="input-unidad_sede">Unidad/UMAE SubSede <span className="text-danger">*</span></label>
									{false && <input type="text" className="form-control"
																	 required
																	 name={'unidad_subsede'}
																	 id="input-unidad_subsede" placeholder="Unidad SubSede" /> }
									<Select
										required
										onChange={setSubSede}
										className={'form-group-die'}
										placeholder={'Unidad SubSede'}
										inputId={'input-unidad-subsede'}
										options={toSelectArray(unidadesSubsede, {value:'id', name:'nombre'})}
									/>
									{errors.unidad_subsede ? 	<p className="help-block">{errors.unidad_subsede}</p> :  null }
								</div>
							</div>
						</div>
					</fieldset>
					<fieldset className={'fieldset'}>
						<div className="row">
							<div className="col-md-3">
								<button type="button" className="btn btn-block btn-default">Cancelar</button>
							</div>
							<div className="col-md-3">
								<button type="submit" className="btn btn-block  btn-primary">Guardar</button>
							</div>
						</div>
					</fieldset>


				</form>
			</FormValidator>
		</>
	)
}

document.addEventListener('DOMContentLoaded', () => {
    const root = creactRoot.createRoot(document.getElementById('residentes-area'));
    root.render(
        <FormResidente
            paises={window.PAISES}
            especialidades={window.ESPECIALIDADES}
            ooads={window.OOADS}
            ciclos_academicos={window.CICLOS_ACADEMICOS}
        />
    );
});
