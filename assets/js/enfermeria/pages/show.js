import React from "react";
import { createRoot } from "react-dom/client";
import Loader from "../../components/Loader/Loader";
//import QR from '../../../images/example/qr.png';
import {getSchemeAndHttpHost} from "../../utils";

function EnfermeriaShow({solicitud, qr}){
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
								<strong>Tipo de periodo:</strong> {solicitud.periodoFormatted}
							</div>
							<div className="col-md-4">
								<strong>Fecha Inicio:</strong> {solicitud.fechaInicioFormatted}
							</div>
							<div className="col-md-4">
								<strong>Fecha Fin:</strong> {solicitud.fechaFinFormatted}
							</div>
						</div>
						<div className={'row mb-15 mt-15'}>
							<div className="col-md-12">
								<h2>QR Generar referencia</h2>
								<img src={`${qr}`} alt="R"/>
								<div>
									<a href={getSchemeAndHttpHost() + `/enfermeria-alumno/login/${solicitud.id}`}>{getSchemeAndHttpHost() + `/enfermeria-alumno/login/${solicitud.id}`}</a>
									<a href={getSchemeAndHttpHost() + `/enfermeria/solicitud/${solicitud.id}/export`}
										 target={'_blank'}
										 className={'btn btn-block btn-primary'}>Exportar</a>
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
											<th>Monto</th>
											<th>Estado</th>
										</tr>
										</thead>
										<tbody>
										{
											solicitud.alumnos.map((item, index) => {
												return (
													<tr key={index}>
														<td>{item.curp}</td>
														<td>{item.nombre}</td>
														<td>{item.email}</td>
														<td>{item.tipo}</td>
														<td>${item.monto}</td>
														<td>{item.statusFormatted}</td>
													</tr>
												)
											})
										}
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


createRoot(document.getElementById('wrapper-page')).render(
	<EnfermeriaShow
		solicitud={window.Solicitud}
		qr={window.Qr}
	/>
)