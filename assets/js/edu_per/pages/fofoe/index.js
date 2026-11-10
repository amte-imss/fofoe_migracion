import React from "react";
import { createRoot } from "react-dom/client";
import api from "../../api";
import Loader from "../../../components/Loader/Loader";
import {getSchemeAndHttpHost} from "../../../utils";


function IndexPage({statusPago, type}) {
	const [isLoading, setIsLoading] = React.useState(true);
	const [pagos, setPagos] = React.useState([]);

	React.useEffect(() => {
		api.getFofoeSolicitudes(statusPago, type).then(data => {
			setPagos(data.data);
		}).finally(() => {
			setIsLoading(false);
		});
	}, [])

	return (
		<>
			<Loader show={isLoading}/>
			<div className="container">
				<div id="simulacion-table">
					<div>
						<div className="row">
							<div className="col-md-6 mt-20">
								<div className="form-group">
									<input name="query" className="form-control" type="text"
												 placeholder="Nombre"
												 width=""/>
								</div>
								<span className="help-block"> </span></div>
							<div className="col-md-2 mt-20">
								<div className="form-group">
									<button className="form-control btn btn-success">Buscar</button>
								</div>
							</div>
						</div>
						<div className="row">
							<div className="panel panel-default col-md-12">
								<div className="panel-body">
									<table className="table">
										<thead className="headers">
										<tr>
											<th>Fecha Creación</th>
											<th>Categoría / Modalidad</th>
											<th>Participante</th>
											<th>Curso</th>
											<th>Monto</th>
											<th>Estatus</th>
											<th></th>
										</tr>
										</thead>
										<tbody>
										{
											pagos.map((item, index) => {
												return (
													<tr key={index}>
														<td>{item.fechaPagoFormatted}</td>
														<td>{item.eduPerSolicitud.duracion.modalidad.name}</td>
														<td>{item.eduPerSolicitud.participant}</td>
														<td>{item.eduPerSolicitud.curso.name}</td>
														<td>$ {item.eduPerSolicitud.amount}</td>
														<td>{item.statusFormatted || 'En proceso'}</td>
														<td>
															<a href={`${getSchemeAndHttpHost()}/fofoe/edu-per/solicitud/${type}/${item.id}`}>Detalle</a>
														</td>
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
	)
}

document.addEventListener('DOMContentLoaded', () => {
	createRoot(document.getElementById('wrapper-page')).render(
		<IndexPage statusPago={window.STATUS_PAGO} type={window.SOLICITUD_TYPE}/>)
})