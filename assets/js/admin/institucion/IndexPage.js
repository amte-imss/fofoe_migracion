import React from 'react';
import { createRoot } from 'react-dom/client';
import { getSchemeAndHttpHost } from '../../utils';
import Loader from '../../components/Loader/Loader';
import ReactPaginate from 'react-paginate';
import Swal from 'sweetalert2';
import '../../components/ReactPaginator/ReactPaginator.css';
import apiInstitucion from './api/ApiInstitucion';

const upper = value => value ? String(value).toUpperCase() : '—';

export default function IndexPage({ isConveniosObserver = false }) {
    const [isLoading, setIsLoading] = React.useState(false);
    const [error, setError] = React.useState('');
    const [searchDraft, setSearchDraft] = React.useState('');
    const [search, setSearch] = React.useState('');
    const [total, setTotal] = React.useState(0);
    const [page, setPage] = React.useState(1);
    const [instituciones, setInstituciones] = React.useState([]);
    const perPage = 50;

    const loadData = () => {
        setIsLoading(true);
        setError('');
        apiInstitucion.paginateInstituciones({ search, page, perPage })
            .then(content => {
                setInstituciones(content.data);
                setTotal(content.total);
            })
            .catch(() => setError('No se pudieron cargar las instituciones. Intenta buscar de nuevo.'))
            .finally(() => setIsLoading(false));
    };

    React.useEffect(() => { loadData(); }, [page, search]);

    const handleSearch = event => {
        event.preventDefault();
        setPage(1);
        setSearch(searchDraft.trim());
    };

    const handleCleanSearch = () => {
        setSearchDraft('');
        setPage(1);
        setSearch('');
    };

    const handleDelete = event => {
        event.preventDefault();
        const institucionId = event.currentTarget.getAttribute('data-value');
        Swal.fire({
            title: '¿Seguro que quieres borrar la institución?',
            html: 'Esta acción no se puede deshacer',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                apiInstitucion.deleteInstitucion(institucionId)
                    .then(content => Swal.fire(content.message, '', 'success'))
                    .then(() => loadData())
                    .catch(error => {
                        if (error.data) {
                            error.data.data.then(({ message }) => Swal.fire('No se pudo eliminar la institución', message, 'error'));
                        } else {
                            Swal.fire('No se pudo eliminar la institución', 'Intenta de nuevo.', 'error');
                        }
                    });
            }
        });
    };

    return <>
        <Loader show={isLoading}/>

        <div className="convenios-card convenios-filters mb-4">
            <div className="convenios-card-header"><i className="ti ti-adjustments-horizontal"/><span>Buscar instituciones</span></div>
            <div className="convenios-card-body">
                <form autoComplete="off" onSubmit={handleSearch}>
                    <div className="row g-3 align-items-end">
                        <div className="col-md-8 col-xl-6">
                            <label htmlFor="institutionSearch" className="form-label">Institución</label>
                            <div className="convenios-search-controls">
                                <input id="institutionSearch" type="search" className="form-control"
                                       value={searchDraft} placeholder="Razón social, RFC o nombre comercial"
                                       onChange={event => setSearchDraft(event.target.value)}/>
                                <button className="btn convenios-btn-primary" type="submit"><i className="ti ti-search me-1"/>Buscar</button>
                            </div>
                        </div>
                    </div>
                </form>
                <button className="btn btn-link convenios-clear px-0 mt-3" type="button" onClick={handleCleanSearch}>
                    <i className="ti ti-filter-off me-1"/>Limpiar búsqueda
                </button>
            </div>
        </div>

        {error && <div className="alert alert-danger" role="alert">{error}</div>}
        <div className="convenios-toolbar mb-3">
            <div><strong>{total.toLocaleString('es-MX')}</strong> instituciones encontradas</div>
            <div className="convenios-toolbar-actions">
                {!isConveniosObserver && <a className="btn convenios-btn-primary" href={getSchemeAndHttpHost('/admin/institucion/new')}>
                    <i className="ti ti-plus me-1"/>Nueva institución</a>}
                <a className="btn btn-outline-success" href={getSchemeAndHttpHost('/admin/institucion/export')}>
                    <i className="ti ti-download me-1"/>Exportar</a>
            </div>
        </div>

        <div className="convenios-card convenios-list-card">
            <div className="table-responsive">
                <table className="table table-hover convenios-table convenios-table--instituciones">
                    <thead><tr>
                        <th>Razón social</th><th>RFC</th><th>Nombre comercial</th><th>Correo</th><th>Sitio web</th>
                        <th>Dirección</th><th>Fax</th><th>Extensión</th><th>Teléfono</th><th>Cédula fiscal</th><th>Acciones</th>
                    </tr></thead>
                    <tbody>
                        {instituciones.map(institucion => <tr key={institucion.id}>
                            <td><span className="convenios-number">{upper(institucion.razonSocial)}</span></td>
                            <td>{upper(institucion.rfc)}</td>
                            <td>{upper(institucion.nombre)}</td>
                            <td>{upper(institucion.correo)}</td>
                            <td>{upper(institucion.sitioWeb)}</td>
                            <td>{upper(institucion.direccion)}</td>
                            <td>{upper(institucion.fax)}</td>
                            <td>{upper(institucion.extension)}</td>
                            <td>{upper(institucion.telefono)}</td>
                            <td>{institucion.cedulaIdentificacion
                                ? <a href={getSchemeAndHttpHost(`/admin/institucion/${institucion.id}/cedula`)} target="_blank" rel="noopener noreferrer">Descargar PDF</a>
                                : 'No disponible'}</td>
                            <td className="convenios-actions-cell"><div className="convenios-row-actions">
                                <a className="btn btn-sm btn-outline-success" href={getSchemeAndHttpHost(`/admin/institucion/${institucion.id}`)}
                                   target="_blank" rel="noopener noreferrer"><i className="ti ti-eye me-1"/>Ver</a>
                                {!isConveniosObserver && <a className="btn btn-sm btn-outline-primary"
                                    href={getSchemeAndHttpHost(`/admin/institucion/edit/${institucion.id}`)}><i className="ti ti-edit me-1"/>Editar</a>}
                                {!isConveniosObserver && <form onSubmit={handleDelete} data-value={institucion.id}>
                                    <button className="btn btn-sm btn-outline-danger" type="submit"><i className="ti ti-trash me-1"/>Eliminar</button>
                                </form>}
                            </div></td>
                        </tr>)}
                        {instituciones.length === 0 && !isLoading && <tr><td colSpan={11} className="convenios-empty">
                            <i className="ti ti-building-off"/><strong>No se encontraron instituciones</strong>
                            <span>Prueba con otro nombre, RFC o razón social.</span>
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
