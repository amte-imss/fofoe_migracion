import React from 'react';
import { createRoot } from 'react-dom/client';
import { getSchemeAndHttpHost } from '../../utils';
import Loader from '../../components/Loader/Loader';
import apiConvenio from './api/ApiConvenio';
import ReactPaginate from 'react-paginate';
import Swal from 'sweetalert2';
import '../../components/ReactPaginator/ReactPaginator.css';

const emptyFilters = { search: '', type: 'active' };

export default function IndexPage({ isConveniosObserver = false }) {
    const [isLoading, setIsLoading] = React.useState(false);
    const [error, setError] = React.useState('');
    const [draftFilters, setDraftFilters] = React.useState(emptyFilters);
    const [appliedFilters, setAppliedFilters] = React.useState(emptyFilters);
    const [total, setTotal] = React.useState(0);
    const [page, setPage] = React.useState(1);
    const [convenios, setConvenios] = React.useState([]);
    const perPage = 50;

    const loadData = () => {
        setIsLoading(true);
        setError('');
        apiConvenio.paginateConvenios({ ...appliedFilters, page, perPage })
            .then(content => {
                setConvenios(content.data);
                setTotal(content.total);
            })
            .catch(() => setError('No se pudieron cargar los convenios. Intenta buscar de nuevo.'))
            .finally(() => setIsLoading(false));
    };

    React.useEffect(() => { loadData(); }, [page, appliedFilters]);

    const handleSearch = event => {
        event.preventDefault();
        setPage(1);
        setAppliedFilters({ ...draftFilters });
    };

    const handleCleanSearch = () => {
        setDraftFilters({ ...emptyFilters });
        setPage(1);
        setAppliedFilters({ ...emptyFilters });
    };

    const handleDelete = event => {
        event.preventDefault();
        const convenioId = event.currentTarget.getAttribute('data-value');
        Swal.fire({
            title: '¿Seguro que quieres borrar el convenio?',
            html: 'Esta acción no se puede deshacer',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                apiConvenio.deleteConvenio(false, convenioId)
                    .then(content => Swal.fire(content.message, '', 'success'))
                    .then(() => loadData())
                    .catch(error => {
                        if (error.data) {
                            error.data.data.then(({ message }) => Swal.fire('No se pudo eliminar el convenio', message, 'error'));
                        } else {
                            Swal.fire('No se pudo eliminar el convenio', 'Intenta de nuevo.', 'error');
                        }
                    });
            }
        });
    };

    return <>
        <Loader show={isLoading}/>

        <div className="convenios-card convenios-filters mb-4">
            <div className="convenios-card-header"><i className="ti ti-adjustments-horizontal"/><span>Filtrar convenios</span></div>
            <div className="convenios-card-body">
                <form autoComplete="off" onSubmit={handleSearch}>
                    <div className="row g-3 align-items-end">
                        <div className="col-md-4 col-xl-3">
                            <label htmlFor="selectType" className="form-label">Estado</label>
                            <select id="selectType" className="form-select" value={draftFilters.type}
                                    onChange={event => setDraftFilters(current => ({ ...current, type: event.target.value }))}>
                                <option value="active">Vigentes</option>
                                <option value="inactive">Vencidos</option>
                                <option value="canceled">Con cancelación anticipada</option>
                                <option value="all">Todos</option>
                            </select>
                        </div>
                        <div className="col-md-8 col-xl-6">
                            <label htmlFor="convenioToSearch" className="form-label">Buscar convenio</label>
                            <div className="convenios-search-controls">
                                <input id="convenioToSearch" type="search" className="form-control"
                                       value={draftFilters.search} placeholder="Nombre, RFC o razón social"
                                       onChange={event => setDraftFilters(current => ({ ...current, search: event.target.value }))}/>
                                <button className="btn convenios-btn-primary" type="submit"><i className="ti ti-search me-1"/>Buscar</button>
                            </div>
                        </div>
                    </div>
                </form>
                <button className="btn btn-link convenios-clear px-0 mt-3" onClick={handleCleanSearch} type="button">
                    <i className="ti ti-filter-off me-1"/>Limpiar filtros
                </button>
            </div>
        </div>

        {error && <div className="alert alert-danger" role="alert">{error}</div>}
        <div className="convenios-toolbar mb-3">
            <div><strong>{total.toLocaleString('es-MX')}</strong> convenios encontrados</div>
            <div className="convenios-toolbar-actions">
                {!isConveniosObserver && <a className="btn convenios-btn-primary" href={getSchemeAndHttpHost('/admin/convenios-especiales/new')}>
                    <i className="ti ti-plus me-1"/>Nuevo convenio</a>}
                <a className="btn btn-outline-success"
                   href={getSchemeAndHttpHost(`/admin/convenios-especiales/exportar?${new URLSearchParams(appliedFilters).toString()}`)}>
                    <i className="ti ti-download me-1"/>Exportar</a>
            </div>
        </div>

        <div className="convenios-card convenios-list-card">
            <div className="table-responsive">
                <table className="table table-hover convenios-table convenios-table--especial">
                    <thead><tr>
                        <th>Número de convenio</th><th>Razón social</th><th>Institución</th><th>Nombre comercial</th>
                        <th>Nivel o grado académico</th><th>Fecha de firma</th><th>Vencimiento</th><th>Acciones</th>
                    </tr></thead>
                    <tbody>
                        {convenios.map(convenio => <tr key={convenio.id}>
                            <td><span className="convenios-number">{convenio.numero || '—'}</span></td>
                            <td>{convenio.razon_social?.toUpperCase() || '—'}</td>
                            <td>{convenio.institucion?.nombre?.toUpperCase() || '—'}</td>
                            <td>{convenio.nombre?.toUpperCase() || '—'}</td>
                            <td>{convenio.nivel_academico?.nombre?.toUpperCase() || '—'}</td>
                            <td>{convenio.fecha_firma_formatted || '—'}</td>
                            <td className={convenio.fecha_vence_convenio_indicator}>{convenio.fecha_vence_convenio_formatted || '—'}</td>
                            <td className="convenios-actions-cell"><div className="convenios-row-actions">
                                <a className="btn btn-sm btn-outline-success" href={getSchemeAndHttpHost(`/admin/convenios-especiales/show?id=${convenio.id}`)}
                                   target="_blank" rel="noopener noreferrer"><i className="ti ti-eye me-1"/>Ver</a>
                                {!isConveniosObserver && <a className="btn btn-sm btn-outline-primary"
                                    href={getSchemeAndHttpHost(`/admin/convenios-especiales/${convenio.id}/edit`)}><i className="ti ti-edit me-1"/>Editar</a>}
                                {!isConveniosObserver && <form onSubmit={handleDelete} data-value={convenio.id}>
                                    <button className="btn btn-sm btn-outline-danger" type="submit"><i className="ti ti-trash me-1"/>Eliminar</button>
                                </form>}
                            </div></td>
                        </tr>)}
                        {convenios.length === 0 && !isLoading && <tr><td colSpan={8} className="convenios-empty">
                            <i className="ti ti-file-off"/><strong>No se encontraron convenios</strong>
                            <span>Prueba con otros filtros o crea un convenio nuevo.</span>
                        </td></tr>}
                    </tbody>
                </table>
            </div>
            <div className="convenios-pagination">
                <ReactPaginate previousLabel="Anterior" nextLabel="Siguiente" breakLabel="..."
                               breakClassName="break-me" pageCount={Math.ceil(total / perPage)}
                               forcePage={page - 1} marginPagesDisplayed={2} pageRangeDisplayed={5}
                               onPageChange={value => setPage(value.selected + 1)}
                               containerClassName="pagination" activeClassName="active"/>
            </div>
        </div>
    </>;
}

document.addEventListener('DOMContentLoaded', () => {
    createRoot(document.querySelector('.react')).render(<IndexPage isConveniosObserver={window.IS_CONVENIOS_OBSERVER}/>);
});
