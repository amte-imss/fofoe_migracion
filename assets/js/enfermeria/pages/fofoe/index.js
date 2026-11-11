import React from "react";
import { createRoot } from "react-dom/client";
import api from "../../api";
import Loader from "../../../components/Loader/Loader";
import {filterSimulacionInstitucionTipoUsuario, getSchemeAndHttpHost} from "../../../utils";


function IndexPage({statusPago, type, year}) {
	const [isLoading, setIsLoading] = React.useState(true);
	const [pagos, setPagos] = React.useState([]);
	const [query, setQuery] = React.useState('');
	const [search, setSearch] = React.useState('');

	React.useEffect(() => {
		api.getFofoeSolicitudes(statusPago, search, type, year).then(data => {
			setPagos(data.data);
		}).finally(() => {
			setIsLoading(false);
		});
	}, [statusPago, search, type, year])

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
												 value={query} onChange={event => setQuery(event.target.value)} width=""/>
								</div>
								<span className="help-block"> </span></div>
							<div className="col-md-2 mt-20">
								<div className="form-group">
									<button className="form-control btn btn-success" onClick={() => setSearch(query)}>Buscar</button>
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
											<th>Escuela Enfermeria</th>
											<th>Alumno</th>
											<th>Tipo</th>
											<th>Periodo</th>
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
														<td>{item.escuelaEnfermeriaSolicitud.solicitud.unidad.nombreEnfermeria}</td>
														<td>{item.escuelaEnfermeriaSolicitud.nombre}</td>
														<td>{item.escuelaEnfermeriaSolicitud.solicitud.typeFormatted}</td>
														<td>{item.escuelaEnfermeriaSolicitud.solicitud.fechaInicioFormatted} - {item.escuelaEnfermeriaSolicitud.solicitud.fechaFinFormatted}</td>
														<td>{item.statusFormatted || 'En proceso'}</td>
														<td>
																	<a href={getSchemeAndHttpHost(`/fofoe/enfermeria/solicitud/${item.id}?year=${encodeURIComponent(year || '')}`)}>Detalle</a>
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

createRoot(document.getElementById('wrapper-page')).render(
	<IndexPage statusPago={window.STATUS_PAGO} type={window.SOLICITUD_TYPE} year={window.YEAR}/>
)
