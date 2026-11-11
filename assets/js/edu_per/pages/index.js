import React from "react";
import { createRoot } from "react-dom/client";
import Loader from "../../components/Loader/Loader";
import {getSchemeAndHttpHost} from "../../utils";
import ApiEduPer from "../api";

function IndexPage(){
	const [isLoading, setIsLoading] = React.useState(false);
	const [years, setYears] = React.useState([])
	const [requests, setRequests] = React.useState([]);

	React.useEffect(() => {
		const yearsData = [];
		const date = new Date();
		yearsData.push(date.getFullYear()-1)
		yearsData.push(date.getFullYear())
		yearsData.push(date.getFullYear()+1)
		setYears(yearsData)
	}, []);

	React.useEffect(() => {
		const  getData = async () => {
			setIsLoading(true);
			ApiEduPer.getRequests().then(({data}) => {
				setRequests(data);
			}).finally(() => {
				setIsLoading(false);
			})
		}
		getData();
	}, []);

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
									<a href="/edu-per/request/create" className="form-control btn btn-primary">Nuevo</a>
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
											<th>Tipo</th>
											<th>Categoría / Modalidad</th>
											<th>Participante</th>
											<th>Curso</th>
											<th>Monto</th>
											<th>Estado</th>
											<th></th>
										</tr>
										</thead>
										<tbody>
										{
											requests && requests.map((item, index) => {
												return (
													<tr key={index}>
														<td>{item.createdAtFormatted}</td>
														<td>{item.id}</td>
														<td>{item.tipoSolicitud.name}</td>
														<td>{item.duracion.modalidad.name}</td>
														<td>{item.participant}</td>
														<td>{item.curso.name}</td>
														<td>$ {item.amount}</td>
														<td>{item.statusFormatted}</td>
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

document.addEventListener('DOMContentLoaded', () => {
	createRoot(document.getElementById('wrapper-page')).render(
		<IndexPage/>)
})