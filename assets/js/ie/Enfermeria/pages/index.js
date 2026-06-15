import React from "react";
import ReactDOM from "react-dom";
import Loader from "../../../components/Loader/Loader";

function EnfermeriaIndex() {
	const [isLoading, setIsLoading] = React.useState(true);

	React.useEffect(() => {
		setTimeout(() => {
			setIsLoading(false);
		}, 500)
	}, [])

	return (
      <div>
          <Loader show={isLoading} />
				<div className="container">
					<div id="die-table">
						<div>
							<div className="row">
								<div className="col-md-6 mt-20">
									<div className="form-group">
										<select name="query" className="form-control">

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
										<a href="/ie/enfermeria/create" className="form-control btn btn-primary">Nuevo</a>
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
												<th>No. Alumnos</th>
												<th>Tipo</th>
												<th></th>
											</tr>
											</thead>
											<tbody>
											<tr>
												<td>2024-01-01</td>
												<td>622461</td>
												<td>300</td>
												<td>Semetral</td>
												<td>
													<a href="/ie/enfermeria/1">Detalle</a>
												</td>
											</tr>
											<tr>
												<td>2023-07-01</td>
												<td>622460</td>
												<td>500</td>
												<td>Semetral</td>
												<td>
													<a href="/ie/enfermeria/1">Detalle</a>
												</td>
											</tr>
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
	ReactDOM.render(
		<EnfermeriaIndex/>,
		document.getElementById('wrapper-page')
	)
})