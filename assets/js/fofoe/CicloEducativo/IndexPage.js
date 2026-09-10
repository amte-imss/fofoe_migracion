import { createRoot } from "react-dom/client";
import React from "react";
import {getSchemeAndHttpHost} from "../../utils";
import ReactPaginate from "react-paginate";
import Swal from "sweetalert2";
import Loader from "../../components/Loader/Loader";
import api from "./api";
import {moneyFormat} from "../../utils";

const HeaderCC = ({setQuery, query, meta, setMeta, handleSearchEvent }) => {
    return(
        <thead>
        <tr>
            <th>OOAD <br /> / UMAE</th>
            <th>Institución Educativa</th>
            <th>No. de Solicitud</th>
            <th>No. de Referencia</th>
            <th>Monto</th>
            <th>Factura</th>
            <th>Fecha Registro de Pago</th>
            <th>Estado</th>
            <th></th>
        </tr>
        <tr style={{background: 'lightgray'}}>
            <th>
                <input type="text" placeholder={'OOAD'}
                       onChange={e => {setQuery(Object.assign(query,{delegacion: e.target.value})); handleSearchEvent()}}/>
            </th>
            <th>
                <input type="text" placeholder="Institución"
                       onChange={e => {setQuery(Object.assign(query,{ institucion : e.target.value})); handleSearchEvent()}}/></th>
            <th><input type="text" placeholder="No. de Solicitud"
                       onChange={e => {setQuery(Object.assign(query,{ no_solicitud : e.target.value})); handleSearchEvent() }}/></th>
            <th><input type="text" placeholder={'No. de Referencia'}
                       onChange={e => {setQuery(Object.assign(query,{referencia: e.target.value})); handleSearchEvent()}}/></th>
            <th><input type="number" placeholder={'Monto'}
                       onChange={e => {setQuery(Object.assign(query,{monto: e.target.value})); handleSearchEvent()}}/></th>
            <th><input type="text" placeholder={'Folio Factura'}
                       onChange={e => {setQuery(Object.assign(query,{factura: e.target.value})); handleSearchEvent()}}/></th>
            <th> </th>
            <th>
                {
                    !meta.estado &&
                    <select className="form-control"
                            defaultValue={meta.estado}
                            onChange={e => { setMeta(Object.assign(meta, {page: 1})); setQuery(Object.assign(query, {estado: e.target.value}));  handleSearchEvent(); }}>
                        <option value="">Todos</option>
                        <option value="En validación de CC">En validación de CC</option>
                        <option value="Solicitud revisada">Solicitud revisada</option>
                        <option value="En validación de montos">En validación de montos</option>
                        <option value="Montos incorrectos">Montos incorrectos</option>
                        <option value="Montos validados">Montos validados</option>
                        <option value="Solicitud Pagada">Solicitud Pagada</option>
                        <option value="En espera de facturación">En espera de facturación</option>
                        <option value="Cancelada">Cancelada</option>
                    </select>
                }
            </th>
            <th> </th>
        </tr>
        </thead>
    )
}

export default function IndexPage() {
    const [isLoading, setIsLoading] = React.useState(false);
    const [meta, setMeta] = React.useState({page: 1, perPage: 50});
    const [query, setQuery] = React.useState({});
    const [solicitudes, setSolicitudes] = React.useState([]);

    const handleSearchEvent = () => {
        setIsLoading(true);
        api.getSolicitudes(meta, query).then(data => {
            setMeta(data.meta);
            setSolicitudes(data.data);
        }).catch(error => {
          console.log(error);
        }).finally(() => {
            setIsLoading(false);
        })
    }
    const showPaginator = () => {
        return meta.total < meta.perPage ? 'none' : 'block';
    }

    React.useEffect(() => {
        handleSearchEvent();
    }, [])

    return (
        <>
            <Loader show={isLoading}/>
            <div className="col-md-3">
                <div className={``}>
                    <label htmlFor="perpage">Tamaño de Página: </label>
                    <select id="perpage" className="form-control"
                            onChange={e => {setMeta(Object.assign(meta, {perPage: e.target.value, page: 1}));  handleSearchEvent(); }}>
                        {/*<option value="1">1</option>*/}
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                    </select>
                    <span className="help-block"> </span>
                </div>
            </div>
            <div className="col-md-12">
                <div className="panel panel-default">
                    <div className={'table-responsive'}>
                        <table className="table table-striped table-fofoe">
                            <HeaderCC
                                setQuery={setQuery}
                                query={query}
                                meta={meta}
                                setMeta={setMeta}
                                handleSearchEvent={handleSearchEvent}
                                categoriaPago={'CC'}
                            />
                            <tbody>
                            {
                                solicitudes.map((solicitud, index) => {
                                    return (
                                        <tr key={index}>
                                            <td>{solicitud.unidad}</td>
                                            <td>{solicitud.institucion}</td>
                                            <td><a href={getSchemeAndHttpHost(`/fofoe/solicitud/${solicitud.id}`)}>{solicitud.noSolicitud}</a></td>
                                            <td>{solicitud.referencia}</td>
                                            <td>{moneyFormat(solicitud.monto)}</td>
                                            <td>{solicitud.factura}</td>
                                            <td>{solicitud.fecha}</td>
                                            <td>{solicitud.status}</td>
                                        </tr>
                                    )
                                })
                            }
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <div className={'col-md-6'} style={{display: (meta.total>0?'block':'none')}}>
                            <br/>
                            <p>Mostrando {(meta.page * meta.perPage) - meta.perPage + 1} {((meta.perPage * meta.page) < meta.total) ?`al ${meta.perPage * meta.page}`: `al ${meta.total}`} de {meta.total}</p>
                        </div>
                        <div style={{display: showPaginator(), textAlign: 'right'}} className={'col-md-6'}>
                            <ReactPaginate
                                previousLabel={'Anterior'}
                                nextLabel={'Siguiente'}
                                breakLabel={'...'}
                                breakClassName={'break-me'}
                                pageCount={Math.ceil(meta.total / meta.perPage)}
                                marginPagesDisplayed={2}
                                pageRangeDisplayed={parseInt(meta.perPage)}
                                onPageChange={value => { setMeta(Object.assign(meta, {page:value.selected + 1})); handleSearchEvent(query)}}
                                containerClassName={'pagination'}
                                subContainerClassName={'pages pagination'}
                                activeClassName={'active'}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}



document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.querySelector('.react');
    if(rootElement){
        const root = createRoot(rootElement);
        root.render(<IndexPage/>);
    }
});
