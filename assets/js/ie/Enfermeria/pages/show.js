import React from "react";
import ReactDOM from "react-dom";
import Loader from "../../../components/Loader/Loader";
import QR from '../../../../images/example/qr.png';

function EnfermeriaShow({}){
	const [isLoading, setIsLoading] = React.useState(true);

	React.useEffect(() => {
		setTimeout(() => {
			setIsLoading(false);
		}, 500)
	}, [])

	return (
		<>
			<div>
				<Loader show={isLoading} />
				<div className="container">
					<div id="die-area">
						<div className="row">
							<div className="col-md-4">
								<strong>Tipo de periodo:</strong> Semestral
							</div>
							<div className="col-md-4">
								<strong>Fecha Inicio:</strong> 2024-01-01
							</div>
							<div className="col-md-4">
								<strong>Fecha Fin:</strong> 2024-06-30
							</div>
						</div>
						<div className={'row mb-15 mt-15'}>
							<div className="col-md-12">
								<h2>QR Generar referencia</h2>
								<img src={QR} alt="R"/>
								<div>
									<a href="#">https://fofoe.desarrollointerno.com/enfermeria</a>
									<a href="#" className={'btn btn-block btn-primary'}>Exportar</a>
								</div>

							</div>
						</div>
						<div className="row mt-30 mb-15">
							<div className="col-md-12">
							<h2>Alumnos registrados</h2>
							</div>
						</div>
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
											<th>Estado</th>
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
											<td>Pendiente de pago</td>
										</tr>
										<tr>
										<td>LOLJ880502HDFHRRF1</td>
											<td>Juan Perez Lopez Lopez</td>
											<td>juanp@mail.com</td>
											<td>Ordinario</td>
											<td>9</td>
											<td>$800.00</td>
											<td>Pago validado</td>
										</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</>
	);
}


document.addEventListener('DOMContentLoaded', () => {
	ReactDOM.render(
		<EnfermeriaShow/>,
		document.getElementById('wrapper-page')
	)
});