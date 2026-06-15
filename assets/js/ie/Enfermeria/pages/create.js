import React from "react";
import ReactDOM from "react-dom";
import Loader from "../../../components/Loader/Loader";
import {FormValidator} from "../../../components/FormValidator/FormValidator";
import InputFile from "../../../components/InputFile/InputFile";
import Papa from 'papaparse';

function EnfermeriaCreate(){
	const [isLoading, setIsLoading] = React.useState(true);

	const [errors, setErrors] = React.useState({});

	const [showTable, setShowTable] = React.useState(false);

	React.useEffect(() => {
		setTimeout(() => {
			setIsLoading(false);
		}, 500)
	}, [])

	const onProcessCsv = (event) => {
		setIsLoading(true);
		const file = event.target.files[0];
		const reader = new FileReader();

		reader.onload = (event) => {
			//console.log(event.target.result);
			setIsLoading(false);
			const json = Papa.parse(event.target.result);
			setShowTable(true);
			//console.log(json);
		};

		reader.readAsText(file);
	}

	return (
      <div>
          <Loader show={isLoading} />
				<div className="container">
					<div id="die-area">
						<FormValidator errors={errors} setErrors={setErrors}>
							<form>
								<fieldset className="fieldset">
									<div className="row">
										<div className="col-md-4">
											<div className={`form-group ${errors["tipoPeriodo"] ? 'has-error' : ''}`}>
												<label htmlFor="input-tipo-periodo">Tipo periodo<span
													className="text-danger">*</span></label>
												<select
													className="form-control"
													id="input-tipo-periodo"
													name={'tipoPeriodo'}
													required
												>
													<option value="">Seleccionar...</option>
													<option value="1">Anual</option>
													<option value="2">Semestral</option>
													<option value="3">Cuatrimestre</option>
												</select>
												<p className="help-block">{errors["tipoPeriodo"]}</p>
											</div>
										</div>
										<div className="col-md-4">
											<div className={`form-group ${errors["fechaInicio"] ? 'has-error' : ''}`}>
												<label htmlFor="input-fecha-inicio">Fecha Inicio<span
													className="text-danger">*</span></label>
												<input type="date" name="fechaInicio" className="form-control" required id={'input-fecha-inicio'}/>
												<p className="help-block">{errors["fechaInicio"]}</p>
											</div>
										</div>
										<div className="col-md-4">
											<div className={`form-group ${errors["fechaFin"] ? 'has-error' : ''}`}>
												<label htmlFor="input-fecha-fin">Fecha Termino<span
													className="text-danger">*</span></label>
												<input type="date" name="fechaFin" className="form-control" required id={'input-fecha-fin'}/>
												<p className="help-block">{errors["fechaFin"]}</p>
											</div>
										</div>
									</div>
									<div className="row">
										<div className="col-md-4">
											<div className={`form-group ${errors["tipoPeriodo"] ? 'has-error' : ''}`}>
												<InputFile handleInputChange={onProcessCsv}/>
											</div>
										</div>
										<div className="col-md-4">
											<div className={`form-group`}>
												<label htmlFor="link-layout"> </label>
												<a id="link-layout" href="#" className={'form-contol'}>Descargar layout de carga</a>
											</div>
										</div>
										<div className="col-md-4">
											<div className={`form-group`}>
												{/*<button className="btn btn-primary btn-block">Enviar</button>*/}
												<a href="/ie/enfermeria/12" className="btn btn-primary btn-block">Enviar</a>
											</div>
										</div>
									</div>
								</fieldset>
							</form>
						</FormValidator>
					</div>
					{ showTable &&
						<>
					<h2>Alumnos registrados</h2>
					<div className="row">
						<div className="panel panel-default col-md-12">
							<div className="panel-body">
								<table className="table">
									<thead className="headers">
									<tr>
										<th>CURP</th>
										<th>Nombre</th>
										<th>Email</th>
										<th>Tipo de alumno</th>
										<th>Promedio</th>
										<th>Monto</th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<td>CASA880502MDFHRRF1</td>
										<td>Ana Gabriela Camacho Suarez</td>
										<td>ana@mail.com</td>
										<td>Ordinario</td>
										<td>9</td>
										<td>$800.00</td>
									</tr>
									<tr>
									<td>LOLJ880502HDFHRRF1</td>
										<td>Juan Perez Lopez Lopez</td>
										<td>juanp@mail.com</td>
										<td>Ordinario</td>
										<td>9</td>
										<td>$800.00</td>
									</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
						</>
					}
				</div>
			</div>
	);
}

document.addEventListener('DOMContentLoaded', () => {
	ReactDOM.render(
		<EnfermeriaCreate/>,
		document.getElementById('wrapper-page')
	)
});