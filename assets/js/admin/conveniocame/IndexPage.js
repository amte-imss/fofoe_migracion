import React from "react";
import { createRoot } from "react-dom/client";
import {getSchemeAndHttpHost} from "../../utils";
import Loader from "../../components/Loader/Loader";
import apiConvenio from "../convenioadmin/api/ApiConvenio";
import ReactPaginate from "react-paginate";
import Swal from "sweetalert2";
import "../../components/ReactPaginator/ReactPaginator.css";

const canEditUmae = (usuario,  convenio) => {
    if(convenio.is_umae && isGranted(usuario.roles, ['ROLE_JDES', 'ROLE_ADMIN'])){
        return true;
    }
}

const canEditCame = (usuario, convenio) => {
    if (convenio.is_came && isGranted(usuario.roles, ['ROLE_CAME'])) {
        return true;
    }
}

const isGranted = (userRoles, searchRoles) => {
    return userRoles.some(r => searchRoles.includes(r));
}


export default function IndexPage({usuario}) {

	const [isLoading, setIsLoading] = React.useState(false);
	const searchRef = React.useRef(null);
	const [total, setTotal] = React.useState(0);
	const [page, setPage] = React.useState(1);
	const [perPage, setPerPage] = React.useState(50);
	const [convenios, setConvenios] = React.useState([]);

	React.useEffect(() => {
		loadData();
	}, [page])

	const loadData = async () => {
		setIsLoading(true);
		apiConvenio.paginateConvenios({
			search: searchRef.current.elements.search.value || '',
			page,
			perPage
		}, true).then(content => {
			setConvenios(content.data)
			setTotal(content.total);
			//setPage(content.page);
			//setPerPage(content.perPage);
		}).finally(() => {
			setIsLoading(false)
		})
	}

	const handleSearch = (event) => {
		event.preventDefault();
		setPage(1)
		loadData()
	}

	const handlePagination = value => {
		console.log(value.selected, value)
		setPage(value.selected + 1);
		//setMeta(Object.assign(meta, {page:value.selected + 1})); handleSearchEvent(query)
	}

	const handleDelete = (event) => {
		event.preventDefault();
		const convenioId = event.target.getAttribute('data-value');
		Swal.fire({
			title: "¿Seguro que quieres borrar el convenio?",
			html: 'Esta acción no se puede deshacer',
			icon: 'question',
			showCancelButton: true,
			confirmButtonText: "Si, Eliminar",
			denyButtonText: 'Cancelar'
		}).then((result) => {

			if (result.isConfirmed) {
				apiConvenio.deleteConvenio(true, convenioId).then(content => {
					Swal.fire(
						content.message,
						'',
						'success'
					).then(() => {
						loadData();
					})
				}).catch(error => {
					if(error.data){
						error.data.data.then(({message}) => {
							Swal.fire(
								'No se pudo eliminar el convenio',
								message,
								'error'
							)
						});
					}
				});
			}
		});
	}

	const handleCleanSearch = () => {
		searchRef.current.reset();
		loadData();
	}

	return (
		<>
			<Loader show={isLoading}/>

			<div className="row mt-3">
				<div className="col-md-12">
					<form className="form-inline text-right" method="post" ref={searchRef} onSubmit={handleSearch}>
						<div className="form-group">
							<input type="text" className="form-control" name="search"
										 id="convenioToSearch" placeholder="Buscar..."/>
						</div>
						<button className="btn btn-outline-secondary" type="submit">
							<i className="fa fa-search"></i>
							<span className="hidden-xs hidden-sm">Buscar</span>
						</button>
						<button className="btn btn-outline-primary ml-1"
										onClick={handleCleanSearch}
										type="button">
							<i className="fa fa-trash"></i>
							<span className="hidden-xs hidden-sm">Limpiar filtros</span>
						</button>
					</form>
				</div>
			</div>
			<div className="row mt-3">
				<div className="col-md-3  mr-btn-headerConAdmin-admin ">
					<a className="btn btn-primary action-new butonConve"
						 href={getSchemeAndHttpHost("/admin/convenioscame/convenioscame/new")}
						 target="_self">Crear convenio</a>
				</div>

				<div className="col-md-3 mr-btn-headerConAdmin-admin ">
					<a className="btn btn-primary action-new butonConve"
						 href={getSchemeAndHttpHost("/admin/convenioscame/convenioscame/exportar")}
						 target="_self">Exportar Convenios</a>
				</div>

				<div id="clearFiltersAdmin" className="col-md-3 mr-btn-headerConAdmin-admin  d-none"><a
					className="btn btn-primary action-new butonConve" href="/admin/convenioscame/convenioscame" target="_self">Limpiar
					Filtros</a></div>
			</div>
			<div className="col-md-12">
				<div className="row">
					<div className="table-responsive scrollListViewTable col-md-12">
						<table className="table table-hover">
							<thead>
							<tr>
								<th>N&uacute;mero Convenio</th>
								<th>Raz&oacute;n Social</th>
								<th>Instituci&oacute;n</th>
                                <th>Nombre Comercial</th>
								<th>Ciclos Académicos</th>
								<th>Nivel o Grado Acad&eacute;mico</th>
								<th>Disciplina</th>
								<th>Carrera</th>
								<th>Fecha firma de Convenio</th>
								<th>Vencimiento del Convenio</th>
								<th>Fecha Carta Compromiso</th>
								<th>Vencimiento de OTA</th>
								<th>Vencimiento RVOE</th>
								<th>Vencimiento COMAEM</th>
								<th>Acciones</th>
							</tr>
							</thead>
							<tbody>
							{
								convenios.map((convenio, index) => {
									let vigencia = convenio.vigencia_text.toString().toUpperCase();
									if(vigencia.toString() === '1'){
										vigencia = "1 AÑO";
									}else if(vigencia.includes('YEAR')){
										vigencia = vigencia.replace('YEAR', 'AÑO');
									}else{
										vigencia = `${vigencia} AÑOS`;
									}
									return (
										<tr key={index}>
											<td>{convenio.numero}</td>
											<td>{convenio.razon_social.toString().toUpperCase()}</td>
											<td>{convenio.institucion.nombre.toString().toUpperCase()}</td>
                                            <td>{convenio.nombre.toString().toUpperCase()}</td>
											<td>{convenio.ciclosAcademicos.map(ciclo => ciclo.nombre.toString().toUpperCase()).join(',')}</td>
											<td>{convenio.nivel_academico ? convenio.nivel_academico.nombre.toString().toUpperCase() : ''}</td>
											<td>{convenio.disciplina ? convenio.disciplina.nombre.toString().toUpperCase() : ''}</td>
											<td>{convenio.carrera ? convenio.carrera.nombre.toString().toUpperCase() : ''}</td>
											<td>{convenio.fecha_firma_formatted}</td>
											<td
												className={convenio.fecha_vence_convenio_indicator}>{convenio.fecha_vence_convenio_formatted}</td>
											<td
												className={convenio.fecha_carta_intencion_indicator}>{convenio.fecha_carta_intencion_formatted}</td>
											<td className={convenio.fecha_vence_ota_indicator}>{convenio.fecha_vence_ota_formatted}</td>
											<td className={convenio.fecha_vence_rveo_indicator}>{convenio.fecha_vence_rvoe_formatted}</td>
											<td className={convenio.fecha_vence_comaem_indicator}>{convenio.fecha_vence_comaem_formatted}</td>
											<td>
												<div className="">
													{(canEditUmae(usuario, convenio) || canEditCame(usuario, convenio) )&&
														<>
															<div className="p-1 col-md-4 btn-sm">
																<form onSubmit={handleDelete} data-value={convenio.id}>
																	<button className="btn btn-danger mb-2" style={{width: "10rem"}}>Eliminar</button>
																</form>
															</div>
															<div className="p-1 col-md-4 btn-sm">
																<a className="btn btn-primary" style={{backgroundColor: "#137474", width: "10rem"}}
																	 href={getSchemeAndHttpHost(`/admin/convenioscame/convenioscame/edit/${convenio.id}`)}
																	 target="_self">
																	Modificar
																</a>
															</div>
														</>
													}
													<div className="p-1 col-md-4 btn-sm">
														<a className="btn btn-primary" style={{backgroundColor: "#137474", "width": "10rem"}}
															 href={getSchemeAndHttpHost(`/admin/convenioscame/convenioscame/show?id=${convenio.id}`)}
															 target="_blank">
															Más Información
														</a>
													</div>
												</div>
											</td>
										</tr>
									)
								})
							}
							</tbody>
						</table>
					</div>
					<ReactPaginate
						previousLabel={'Anterior'}
						nextLabel={'Siguiente'}
						breakLabel={'...'}
						breakClassName={'break-me'}
						pageCount={total / perPage}
						marginPagesDisplayed={2}
						pageRangeDisplayed={parseInt(perPage)}
						onPageChange={handlePagination}
						containerClassName={'pagination'}
						subContainerClassName={'pages pagination'}
						activeClassName={'active'}
					/>
				</div>
			</div>
		</>
	)
}

document.addEventListener('DOMContentLoaded', () => {
	createRoot(document.querySelector('.react')).render(
		<IndexPage usuario={window.Usuario}/>,
	)});