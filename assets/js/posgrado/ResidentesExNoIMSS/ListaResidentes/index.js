import React from "react";
import createRoot from "react-dom/client";
import ReactPaginate from "react-paginate";
import {dateFormat, getEstatusPago, getLastPago, getSchemeAndHttpHost} from "../../../utils";
import Loader from "../../../components/Loader/Loader";
import SeleccionadorCiclos from "../components/SeleccionadorCiclos";

const ListaResidentes = ({metadata}) => {
    const {useState, useEffect} = React
    const [residentes, setResidentes] = useState([])
    const [isLoading, setIsLoading] = useState(true)
    let ciclos = metadata.ciclos && metadata.ciclos.length > 0 ? [{ciclo: ''}, ...metadata.ciclos] : [{ciclo: ''}]
    const [meta, setMeta] = React.useState({
        total: metadata.total,
        page: metadata.page,
        perPage: metadata.perPage,
        ciclo: ciclos[0].ciclo,
        query: ''
    });

    useEffect(() => {
        setIsLoading(true);
        refreshResidentes()
    }, [])

    const handleSearchEvent = () => {
        setIsLoading(true);
        refreshResidentes()
    }

    const refreshResidentes = () => {
        fetch(`${getSchemeAndHttpHost()}/posgrado/api/residentes/no_imss?page=${meta.page}&perPage=${meta.perPage}&ciclo=${meta.ciclo}&query=${meta.query}`)
            .then(response => {
                    return response.json()
                },
                error => {
                    console.error(error)
                })
            .then(json => {
                setResidentes(json.data);
                setMeta(Object.assign(meta, json.meta))
            })
            .finally(() => {
                setIsLoading(false)
            });
    }

    const showPaginator = () => {
        return meta.total < meta.perPage ? 'none' : 'block';
    }

    return (
        isLoading ?
            <Loader show={isLoading}/>
            :
            <div>
                {
                    true || metadata.ciclos.length > 0 ?
                        <SeleccionadorCiclos
                            ciclos={ciclos}
                            setMeta={setMeta}
                            handleSearchEvent={handleSearchEvent}
                            meta={meta}
                        />
                        : null
                }
                <div className="row">
                    <div className="panel panel-default col-md-12">
                        <div className="panel-body">
                            <table className='table'>
                                <thead className='headers'>
                                <tr>
                                    <th>Núm Oficio</th>
                                    <th>Nombre</th>
                                    <th>Especialidad</th>
                                    {false && <th>Nacionalidad</th>}
                                    <th>Meses de Rotación</th>
                                    <th>Estatus Pago</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                {
                                    residentes.length !== 0 ?
                                        residentes.map((result, index) => {
                                            let residente = result[0]
                                            return (
                                                <tr key={index}>
                                                    <td>{residente.folio}</td>
                                                    <td>{residente.usuario.nombre} {residente.usuario.apellidoPaterno} {residente.usuario.apellidoMaterno}</td>
                                                    <td>{residente.especialidad}</td>
                                                    {false && <td>{residente.nacionalidad}</td>}
                                                    <td>{dateFormat(result.fechaInicio)} - {dateFormat(result.fechaTermino)}</td>
                                                    <td>{residente.residencias[0].estatus != 'Pendiente factura FOFOE' ? getEstatusPago(getLastPago(residente.residencias[0].pagos), residente.residencias[0].pagos.length) : 'Pendiente factura FOFOE'}</td>
                                                    <td><a
                                                        href={`${getSchemeAndHttpHost()}/posgrado/residentes/no_imss/${residente.id}/detalle`}> detalle</a>
                                                    </td>
                                                </tr>
                                            )
                                        })
                                        :
                                        <tr>
                                            <td className='text-center' colSpan={7}>No hay registros disponibles</td>
                                        </tr>
                                }
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div className="row">
                    <div className={'col-md-12'} style={{display: (meta.total > 0 ? 'block' : 'none')}}>
                        <br/>
                        <p>Mostrando {(meta.page * meta.perPage) - meta.perPage + 1} {((meta.perPage * meta.page) < meta.total) ? `al ${meta.perPage * meta.page}` : `al ${meta.total}`} de {meta.total}</p>
                    </div>
                    <div style={{display: showPaginator(), textAlign: 'right'}} className={'col-md-12'}>
                        <ReactPaginate
                            previousLabel={'Anterior'}
                            nextLabel={'Siguiente'}
                            breakLabel={'...'}
                            breakClassName={'break-me'}
                            pageCount={meta.total / meta.perPage}
                            marginPagesDisplayed={2}
                            pageRangeDisplayed={parseInt(meta.perPage)}
                            onPageChange={value => {
                                setMeta(Object.assign(meta, {page: value.selected + 1}));
                                handleSearchEvent()
                            }}
                            containerClassName={'pagination'}
                            subContainerClassName={'pages pagination'}
                            activeClassName={'active'}
                        />
                    </div>
                </div>

            </div>
    )
}

document.addEventListener('DOMContentLoaded', () => {
    const root = createRoot.createRoot(document.getElementById('residentes-table'));
    root.render(
        <ListaResidentes
            metadata={window.META}
        />
    );
})
