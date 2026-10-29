import React from "react";
import { createRoot } from "react-dom/client";
import {getSchemeAndHttpHost} from "../../utils";
import Loader from "../../components/Loader/Loader";
import apiConvenio from "./api/ApiConvenio";
import ReactPaginate from "react-paginate";
import Swal from "sweetalert2";
import "../../components/ReactPaginator/ReactPaginator.css";

export default function IndexPage({isConveniosObserver = false}) {

	const emptyFilters = {search: '', type: 'active', startDate: '', endDate: ''};
	const [isLoading, setIsLoading] = React.useState(false);
	const [error, setError] = React.useState('');
	const [draftFilters, setDraftFilters] = React.useState(emptyFilters);
	const [appliedFilters, setAppliedFilters] = React.useState(emptyFilters);
	const [total, setTotal] = React.useState(0);
	const [page, setPage] = React.useState(1);
	const perPage = 50;
	const [convenios, setConvenios] = React.useState([]);

	React.useEffect(() => {
		loadData();
	}, [page, appliedFilters])

	const loadData = async () => {
		setIsLoading(true);
		setError('');
		apiConvenio.paginateConvenios({
			...appliedFilters,
			page,
			perPage
		}).then(content => {
			setConvenios(content.data)
			setTotal(content.total);
		}).catch(() => {
			setError('No se pudieron cargar los convenios. Intenta buscar de nuevo.');
		}).finally(() => {
			setIsLoading(false)
		})
	}

	const handleSearch = (event) => {
		event.preventDefault();
		setPage(1);
		setAppliedFilters({...draftFilters});
	}

	const handlePagination = value => {
		setPage(value.selected + 1);
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
				apiConvenio.deleteConvenio(false, convenioId).then(content => {
					Swal.fire(
						content.message,
						'',
						'success'
					).then(() => {
						loadData();
					})
				}).catch(error => {
					if (error.data) {
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
		setDraftFilters({...emptyFilters});
		setPage(1);
		setAppliedFilters({...emptyFilters});
	}

	const handleChangeStatus = (convenioObj) => {
		const title = convenioObj.status === 'active' ? '¿Seguro que quieres marcar terminación anticipada?' : '¿Seguro que quieres quitar la terminación anticipada?';
		Swal.fire({
			title,
			html: 'Esta acción no se puede deshacer',
			icon: 'question',
			showCancelButton: true,
			confirmButtonText: "Si, continuar",
			denyButtonText: 'Cancelar'
		}).then((result) => {
			if (result.isConfirmed) {
				const newStatus = convenioObj.status === 'active' ? 'canceled' : 'active';
				apiConvenio.setTerminacionAnticipada(convenioObj.id, newStatus).then(content => {
					Swal.fire(
						content.message,
						'',
						'success'
					).then(() => {
						loadData();
					})
				}).catch(error => {
					if (error.data) {
						error.data.data.then(({message}) => {
							Swal.fire(
								'No se pudo modificar el convenio',
								message,
								'error'
							)
						});
					}
				});
			}
		})
	}


	return (
		<>
			<Loader show={isLoading}/>

			<div className="convenios-card convenios-filters mb-4">
				<div className="convenios-card-header"><i className="ti ti-adjustments-horizontal"/><span>Filtrar convenios</span></div>
				<div className="convenios-card-body">
				<div className="row g-3 align-items-end">
					<div className="col-md-6 col-xl-3">
						<label htmlFor="selectType" className="form-label">Estado</label>
						<select name="type" id={'selectType'}
										value={draftFilters.type}
										onChange={e => setDraftFilters(current => ({...current, type: e.target.value}))}
									className={'form-select'}>
							<option value="active">Vigentes</option>
							<option value="inactive">Vencidos</option>
							<option value="canceled">Con cancelación anticipada</option>
							<option value="intencion">Con Carta de Intención</option>
							<option value="all">Todas</option>
						</select>
				</div>
				<div className="col-md-6 col-xl-3">
					<label htmlFor="vigenciaInicio" className="form-label">Vigencia desde</label>
					<input id="vigenciaInicio" value={draftFilters.startDate} onChange={e => setDraftFilters(current => ({...current, startDate: e.target.value}))} className="form-control" type="date"/>
				</div>
				<div className="col-md-6 col-xl-3">
					<label htmlFor="vigenciaFin" className="form-label">Vigencia hasta</label>
					<input id="vigenciaFin" value={draftFilters.endDate} onChange={e => setDraftFilters(current => ({...current, endDate: e.target.value}))} className="form-control" type="date"/>
				</div>
				<div className="col-md-6 col-xl-3">
					<form className="convenios-search-form" autoComplete="off" onSubmit={handleSearch}>
						<label htmlFor="convenioToSearch" className="form-label">Buscar convenio</label>
						<div className="convenios-search-controls">
							<input type="search" className="form-control" name="search" value={draftFilters.search}
										 onChange={e => setDraftFilters(current => ({...current, search: e.target.value}))}
									 id="convenioToSearch" placeholder="Nombre, número o institución"/>
							<button className="btn convenios-btn-primary" type="submit"><i className="ti ti-search me-1"/>Buscar</button>
						</div>
					</form>
				</div>
				</div>
				<button className="btn btn-link convenios-clear px-0 mt-3" onClick={handleCleanSearch} type="button"><i className="ti ti-filter-off me-1"/>Limpiar filtros</button>
				</div>
			</div>
			{error && <div className="alert alert-danger" role="alert">{error}</div>}
			<div className="convenios-toolbar mb-3">
				<div><strong>{total.toLocaleString('es-MX')}</strong> convenios encontrados</div>
				<div className="convenios-toolbar-actions">
                { !isConveniosObserver &&
						<a className="btn convenios-btn-primary"
						 href={getSchemeAndHttpHost("/admin/conveniosadmin/conveniosadmin/new")}
							 target="_self"><i className="ti ti-plus me-1"/>Nuevo convenio</a> }
					<a className="btn btn-outline-success"
						 href={getSchemeAndHttpHost(`/admin/conveniosadmin/conveniosadmin/exportar?${new URLSearchParams({type: appliedFilters.type, search: appliedFilters.search}).toString()}`)}
						 target="_self"><i className="ti ti-download me-1"/>Exportar</a>
				</div>
			</div>
			<div className="convenios-card convenios-list-card">
				<div className="table-responsive">
						<table className="table table-hover convenios-table">
							<thead>
							<tr>
								<th>N&uacute;mero Convenio</th>
								<th>Raz&oacute;n Social</th>
								<th>Institución</th>
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
									return (
											<tr key={convenio.id || index}>
												<td><span className="convenios-number">{convenio.numero || '—'}</span></td>
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
												<td className="convenios-actions-cell">
													<div className="convenios-row-actions">
														<a className="btn btn-sm btn-outline-success" href={getSchemeAndHttpHost(`/admin/conveniosadmin/conveniosadmin/show?id=${convenio.id}`)} target="_blank" rel="noopener noreferrer"><i className="ti ti-eye me-1"/>Ver</a>
														{!isConveniosObserver && <a className="btn btn-sm btn-outline-primary" href={getSchemeAndHttpHost(`/admin/conveniosadmin/conveniosadmin/edit/${convenio.id}`)}><i className="ti ti-edit me-1"/>Editar</a>}
														{!isConveniosObserver && ['active', 'canceled'].includes(convenio.status) && <button className="btn btn-sm btn-outline-warning" onClick={() => handleChangeStatus(convenio)} type="button">{convenio.status === 'active' ? 'Terminar' : 'Reactivar'}</button>}
														{!isConveniosObserver && <form onSubmit={handleDelete} data-value={convenio.id}><button className="btn btn-sm btn-outline-danger" type="submit"><i className="ti ti-trash me-1"/>Eliminar</button></form>}
													</div>
											</td>
										</tr>
									)
								})
							}
								{convenios.length === 0 && !isLoading && <tr><td colSpan={15} className="convenios-empty"><i className="ti ti-file-off"/><strong>No se encontraron convenios</strong><span>Prueba con otros filtros o crea un convenio nuevo.</span></td></tr>}
								</tbody>
						</table>
					</div>
						<div className="convenios-pagination">
					<ReactPaginate
						previousLabel={'Anterior'}
						nextLabel={'Siguiente'}
						breakLabel={'...'}
						breakClassName={'break-me'}
						pageCount={Math.ceil(total / perPage)}
						forcePage={page - 1}
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
		<IndexPage isConveniosObserver={window.IS_CONVENIOS_OBSERVER}/>,
	)});
