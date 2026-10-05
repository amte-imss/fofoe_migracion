import ReactDOM from "react-dom";
import React from "react";
import {FormValidator} from "../../../components/FormValidator/FormValidator";

const FormResidente = () => {

	const [errors, setErrors] = React.useState({});

	const handleSubmit = (event) => {
		event.preventDefault();
	}

	return (
		<>
			<FormValidator errors={errors} setErrors={setErrors}>
				<form onSubmit={handleSubmit}>
					<fieldset className="fieldset">
						<legend>Datos Generales</legend>
						<div className="row">
							<div className="col-md-4">
								<div className={`form-group ${errors.name ? 'has-error' : ''}`}>
									<label htmlFor="input-name">Nombre(s) <span className="text-danger">*</span></label>
									<input type="text" className="form-control" id="input-name"
												 required
												 name={'name'}
												 placeholder="Nombres" />
									{errors.name ? <p className="help-block">{errors.name}</p> : null }
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors.last_name ? 'has-error': ''}`}>
									<label htmlFor="input-last_name">Apellido Paterno <span className="text-danger">*</span></label>
									<input type="text" className="form-control"
												 required
												 name={'last_name'}
												 id="input-last_name" placeholder="Apellido Paterno" />
									{errors.last_name ? <p className="help-block">{errors.last_name}</p> : null}
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors.other_name ? 'has-error' : ''}`}>
									<label htmlFor="input-last_name">Apellido Materno</label>
									<input type="text" className="form-control"
												 name={'other_name'}
												 id="input-other_name" placeholder="Apellido Materno" />
									{errors.other_name ? <p className="help-block">{errors.other_name}</p> : null }
								</div>
							</div>
						</div>
						<div className="row">
							<div className="col-md-4">
								<div className={`form-group ${errors.curp ? 'has-error': ''}`}>
									<label htmlFor="input-curp">CURP <span className="text-danger">*</span></label>
									<input type="text" className="form-control"
												 required name={'curp'}
												 id="input-curp" placeholder="CURP" />
									{errors.curp ? <p className="help-block">{errors.curp}</p> : null}
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors.email ? 'has-error' : ''}`}>
									<label htmlFor="input-email">Correo Electrónico <span className="text-danger">*</span></label>
									<input type="email" className="form-control"
												 required name={'email'}
												 id="input-email" placeholder="juan@mail.com" />
									{errors.email ? <p className="help-block">{errors.email}</p> : null }
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors.phone ? 'has-error' : ''}`}>
									<label htmlFor="input-phone">Teléfono</label>
									<input type="text" className="form-control"
												 name={'phone'}
												 id="input-phone" placeholder="55-5555-5555" />
									{errors.phone ? <p className="help-block">{errors.phone}</p> : null }
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors.nacionalidad ? 'has-errors' : ''}`}>
									<label htmlFor="input-nacionalidad">Nacionalidad <span className="text-danger">*</span></label>
									<input type="text" className="form-control"
												 required
												 name={'nacionalidad'}
												 id="input-nacionalidad" placeholder="Nacionalidad" />
									{errors.nacionalidad ? <p className="help-block">{errors.nacionalidad}</p> : null }
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
												 name={'solicitud'}
												 id="input-solicitud" placeholder="Tipo de solicitud" />
									{errors.solicitud ? <p className="help-block">{errors.solicitud}</p> : null }
								</div>
							</div>

							<div className="col-md-4">
								<div className={`form-group ${errors.grado_academico ? 'has-error': ''}`}>
									<label htmlFor="input-grado_academico">Grado Académico <span className="text-danger">*</span></label>
									<input type="text" className="form-control"
												 required
												 name={'grado_academico'}
												 id="input-grado_academico" placeholder="Grado Académicos" />
									{errors.grado_academico ? <p className="help-block">{errors.grado_academico}</p> : null}
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group ${errors.especialidad}`}>
									<label htmlFor="input-especialidad">Especialidad <span className="text-danger">*</span></label>
									<input type="text" className="form-control"
												 required
												 name={'especialidad'}
												 id="input-especialidad" placeholder="Especialidad" />
									{errors.especialidad ? <p className="help-block">{errors.especialidad}</p> : null}
								</div>
							</div>
						</div>
						<div className="row">
							<div className="col-md-3">
								<div className={`form-group ${errors.fecha_inicio ? 'has-error': ''}`}>
									<label htmlFor="input-solicitud">Inicio <span className="text-danger">*</span></label>
									<input type="date" className="form-control"
												 required
												 name={'fecha_inicio'}
												 id="input-fecha_inicio" />
									{errors.fecha_inicio ?	<p className="help-block">{errors.fecha_inicio}</p> : null}
								</div>
							</div>
							<div className="col-md-3">
								<div className={`form-group ${errors.fecha_termino ? 'has-error' : ''}`}>
									<label htmlFor="input-termino">Termino <span className="text-danger">*</span></label>
									<input type="date" className="form-control"
												 required
												 name={'fecha_termino'}
												 id="input-fecha_termino" />
									{errors.fecha_termino ? <p className="help-block">{errors.fecha_termino}</p> : null }
								</div>
							</div>
							<div className="col-md-4">
								<div className={`form-group`}>
									<label htmlFor="input-folio_imss">Folio IMSS</label>
									<input type="text" className="form-control"
												 name={'folio_imss'}
												 id="input-folio_imss" />
									{errors.folio_imss ? 	<p className="help-block">{errors.folio_imss}</p> : null }
								</div>
							</div>
						</div>
						<div className="row">
							<div className="col-md-6">
								<div className={`form-group ${errors.ooad ? 'has-error' : ''}`}>
									<label htmlFor="input-ooad">OOAD <span className="text-danger">*</span></label>
									<input type="text" className="form-control"
												 required
												 name={'ooad'}
												 id="input-ooad" placeholder="OOAD" />
									{errors.ooad ? 	<p className="help-block">{errors.ooad}</p> : null }
								</div>
							</div>
							<div className="col-md-6">
								<div className={`form-group ${errors.unidad_sede ? 'has-error' : ''}`}>
									<label htmlFor="input-unidad_sede">Unidad Sede <span className="text-danger">*</span></label>
									<input type="text" className="form-control"
												 required
												 name={'unidad_sede'}
												 id="input-unidad_sede" placeholder="Unidad Sede" />
									{errors.unidad_sede ? 	<p className="help-block">{errors.unidad_sede}</p> :  null }
								</div>
							</div>
							<div className="col-md-6">
								<div className={`form-group ${errors.subsede ? 'has-error' : ''}`}>
									<label htmlFor="input-subsede">Subsede</label>
									<input type="text" className="form-control"
												 name={'subsede'}
												 id="input-subsede" placeholder="Subsede" />
									{errors.subsede ? <p className="help-block">{errors.subsede}</p> : null}
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
	ReactDOM.render(
		<FormResidente
		/>,
		document.getElementById('residentes-area')
	)
})