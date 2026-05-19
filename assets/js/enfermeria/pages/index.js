import React from "react";
import { createRoot } from "react-dom/client";
import Loader from "../../components/Loader/Loader";
import {getSchemeAndHttpHost} from "../../utils";
import useFetch from "../../hooks/useFetch";

function EnfermeriaIndex() {
	const [isLoading, setIsLoading] = React.useState(true);

	const [solicitudUrl, setSolicitudUrl ] = React.useState('');

	const [years, setYears] = React.useState([])

	const [selectedYear, setSelectedYear] = React.useState('')

	const [solicitudes] = useFetch(solicitudUrl);
	//const [solicitudes, s] = React.useState({data:[]})

	React.useEffect(() => {
		const yearsData = [];
		const date = new Date();
		yearsData.push(date.getFullYear()-1)
		yearsData.push(date.getFullYear())
		yearsData.push(date.getFullYear()+1)
		setYears(yearsData)
	}, [])

	React.useEffect(() => {
		setTimeout(() => {
			setIsLoading(false);
		}, 1000)
		setSolicitudUrl(getSchemeAndHttpHost() + '/enfermeria/solicitud/api?query=' + (selectedYear))
	}, [selectedYear])

	return (
      <div>
          <Loader show={isLoading} />
				<div className="container">
					<div id="die-table">
						<div>
							<div className="row">
								<div className="col-md-6 mt-20">
									<div className="form-group">
										<select name="query"
														onInput={(event) => {setSelectedYear(event.target.value)}}
														className="form-control">
											<option value=""></option>
											{
												years.map((item, index) =>
													<option value={item} key={index}>{item}</option>
												)
											}
										</select>
									</div>
									<span className="help-block"> </span></div>
								<div className="col-md-2 mt-20">
									<div className="form-group">
										<button className="form-control btn btn-success">Buscar</button>
									</div>
								</div>
								<div className="col-md-2 mt-20">
									<div className="form-group">
										<a href={getSchemeAndHttpHost("/enfermeria/solicitud/create")} className="form-control btn btn-primary">Nuevo</a>
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
												<th>Id</th>
                                                <th>Escuela de Enfermería</th>
												<th>No. Alumnos</th>
												<th>Tipo</th>
												<th></th>
											</tr>
											</thead>
											<tbody>
											{
												solicitudes && solicitudes.data.map((item, index) => {
													return (
														<tr key={index}>
															<td>{item[0].createdAtFormatted}</td>
															<td>{item[0].id}</td>
                                                            <td>{item[0].unidad.nombreEnfermeria}</td>
															<td> {item.totalAlumnos} </td>
															<td>{item[0].periodoFormatted}</td>
															<td>
																<a href={getSchemeAndHttpHost(`/enfermeria/solicitud/${item[0].id}`)}>Detalle</a>
															</td>
														</tr> )
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
			</div>
	);
}

createRoot(document.getElementById('wrapper-page')).render(<EnfermeriaIndex/>)