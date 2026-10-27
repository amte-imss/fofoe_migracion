import React from "react";
import { createRoot } from "react-dom/client";
import {getSchemeAndHttpHost} from "../../utils";
import Loader from "../../components/Loader/Loader";
import ReactPaginate from "react-paginate";
import Swal from "sweetalert2";
import "../../components/ReactPaginator/ReactPaginator.css";
import apiInstitucion from "./api/ApiInstitucion";

export default function IndexPage({isConveniosObserver = false}) {

    const [isLoading, setIsLoading] = React.useState(false);
    const searchRef = React.useRef(null);
    const [total, setTotal] = React.useState(0);
    const [page, setPage] = React.useState(1);
    const [perPage, setPerPage] = React.useState(50);
    const [instituciones, setInstituciones] = React.useState([]);

    React.useEffect(() => {
        loadData();
    }, [page])

    const loadData = async () => {
        setIsLoading(true);
        apiInstitucion.paginateInstituciones({
            search: searchRef.current.elements.search.value || '',
            page,
            perPage
        }).then(content => {
            setInstituciones(content.data)
            setTotal(content.total);
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
        setPage(value.selected + 1);
    }

    const handleDelete = (event) => {
        event.preventDefault();
        const institucionId = event.target.getAttribute('data-value');
        Swal.fire({
            title: "¿Seguro que quieres borrar la institución?",
            html: 'Esta acción no se puede deshacer',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: "Si, Eliminar",
            denyButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                apiInstitucion.deleteInstitucion(institucionId).then(content => {
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
                                'No se pudo eliminar la institución',
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

    // Método para generar el enlace completo
    const getDownloadLink = (institucion) => {
        const filePath = `${getSchemeAndHttpHost("/admin/institucion/")}${institucion.id}/cedula`;
        return filePath;
    }

    return (
        <>
            <Loader show={isLoading}/>

            <div className="row mt-3">
                <div className="col-md-12">
                    <form className="form-inline text-right"
                          autoComplete={'off'}
                          method="post" ref={searchRef} onSubmit={handleSearch}>
                        <div className="form-group">
                            <input type="text" className="form-control" name="search"
                                   placeholder="Buscar..."/>
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
                { !isConveniosObserver &&
                <div className="col-md-3  mr-btn-headerConAdmin-admin ">
                    <a className="btn btn-primary action-new butonConve"
                       href={getSchemeAndHttpHost("/admin/institucion/new")}
                       target="_self">Crear Institución</a>
                </div> }
                <div className="col-md-4 mr-btn-headerConAdmin-admin ">
                    <a className="btn btn-primary action-new butonConve"
                       style={{width: "100%"}}
                       href={getSchemeAndHttpHost("/admin/institucion/export")}
                       target="_self">Exportar Instituciones</a>
                </div>
                <div id="clearFiltersAdmin" className="col-md-3 mr-btn-headerConAdmin-admin  d-none"><a
                  className="btn btn-primary action-new butonConve" href="/admin/instituto" target="_self">Limpiar
                    Filtros</a></div>
            </div>
            <div className="col-md-12">
                <div className="row">
                    <div className="table-responsive scrollListViewTable col-md-12">
                        <table className="table table-hover">
                            <thead>
                            <tr>
                                <th>Razón Social</th>
                                <th>RFC</th>
                                <th>Nombre Comercial</th>
                                <th>Email</th>
                                <th>Sitio Web</th>
                                <th>Dirección</th>
                                <th>Fax</th>
                                <th>Extensión</th>
                                <th>Teléfono</th>
                                <th>Cédula Fiscal</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            {
                                instituciones.map((instituto, index) => {
                                    return (
                                        <tr key={index}>
                                            <td>{instituto.razonSocial.toString().toUpperCase()}</td>
                                            <td>{instituto.rfc.toString().toUpperCase()}</td>
                                            <td>{instituto.nombre.toString().toUpperCase()}</td>
                                            <td>{instituto.correo ? instituto.correo.toString().toUpperCase() : ''}</td>
                                            <td>{instituto.sitioWeb ? instituto.sitioWeb.toString().toUpperCase() : ''}</td>
                                            <td>{instituto.direccion ? instituto.direccion.toString().toUpperCase() : ''}</td>
                                            <td>{instituto.fax ? instituto.fax.toString().toUpperCase() : ''}</td>
                                            <td>{instituto.extension ? instituto.extension.toString().toUpperCase() : ''}</td>
                                            <td>{instituto.telefono ? instituto.telefono.toString().toUpperCase() : ''}</td>
                                            <td>
                                                {
                                                    instituto.cedulaIdentificacion ?
                                                        <a href={getDownloadLink(instituto)}
                                                           target="_blank">
                                                            Descargar PDF
                                                        </a>
                                                        :
                                                        'No disponible'
                                                }
                                            </td>
                                            <td>
                                                <div className="">
                                                    { !isConveniosObserver &&
                                                    <div className="p-1 col-md-4 btn-sm">
                                                        <form onSubmit={handleDelete} data-value={instituto.id}>
                                                            <button className="btn btn-danger mb-2" style={{width: "10rem"}}>Eliminar</button>
                                                        </form>
                                                    </div> }
                                                    { !isConveniosObserver &&
                                                    <div className="p-1 col-md-4 btn-sm">
                                                        <a className="btn btn-primary" style={{backgroundColor: "#137474", width: "10rem"}}
                                                           href={getSchemeAndHttpHost(`/admin/institucion/edit/${instituto.id}`)}
                                                           target="_self">
                                                            Modificar
                                                        </a>
                                                    </div> }
                                                    <div className="p-1 col-md-4 btn-sm">
                                                        <a className="btn btn-primary" style={{backgroundColor: "#137474", "width": "10rem"}}
                                                           href={getSchemeAndHttpHost(`/admin/institucion/${instituto.id}`)}
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
        <IndexPage isConveniosObserver={window.IS_CONVENIOS_OBSERVER}/>,
    )});