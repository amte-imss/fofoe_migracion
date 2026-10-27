import { createRoot } from "react-dom/client";
import * as React from 'react'
import Loader from "../../components/Loader/Loader";
import ReactPaginate from "react-paginate";
import './index.scss';
import {getSchemeAndHttpHost} from "../../utils";
import AccionFofoe from "./components/AccionFofoe";
import EstadosPago from "./components/EstadosPago";
import Facturas from "./components/Facturas";
import SeleccionadorOrdenReferencias from "./components/SeleccionadorOrdenReferencias";
import {FOFOE_TIPO_PAGOS} from "../constants";
import HeaderTableReferencias from "./components/HeaderTableReferencias";
import BodyTableReferencias from "./components/BodyTableReferencias";

const PagoIndex = (props) => {
    const [pagos, setPagos] = React.useState(props.pagos ? props.pagos : [])
    const [isLoading, setIsLoading] = React.useState(false)
    const [meta, setMeta] = React.useState(props.meta);
    const [query, setQuery] = React.useState({});

    const handleSearchEvent = () => {
        setIsLoading(true);
        let querystring = '';
        for (const i in query) {
            if(query[i].trim()!== ''){
                querystring += `${i}=${query[i]}&`;
            }
        }

        fetch(`${getSchemeAndHttpHost()}/fofoe/api/pago${FOFOE_TIPO_PAGOS[props.categoriaPago].sufijo_api}?${querystring}page=${meta.page}&perPage=${meta.perPage}&estado=${meta.estado}`)
            .then(response => { return response.json()}, error => {console.error(error)})
            .then(json => {setPagos(json.data); setMeta(json.meta)})
            .finally(() => { setIsLoading(false)});

    }

    const showPaginator = () => {
        return meta.total < meta.perPage ? 'none' : 'block';
    }

    return (
        <>
            <Loader show={isLoading}/>
            <div className="col-md-2">
                <div className={``}>
                    <label htmlFor="year">Año: </label>
                    <select id="year" className="form-control"
                            defaultValue={props.meta.year}
                            onChange={e => {setQuery(Object.assign(query, {year: e.target.value}));  handleSearchEvent(); }}>
                        <option key={'2021'} value={'2021'} >2021</option>
                        {props.years.map(item => {
                            return (<option key={`${item.year}`} value={`${item.year}`}>{item.year}</option>)
                        })}
                    </select>
                    <span className="help-block"> </span>
                </div>
            </div>
            <SeleccionadorOrdenReferencias
              handleSearchEvent={handleSearchEvent}
              setQuery={setQuery}
              query={query}
              categoriaPago={props.categoriaPago}
              />
            <div className="col-md-4"> </div>
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
                            <HeaderTableReferencias
                            setQuery={setQuery}
                            query={query}
                            meta={meta}
                            setMeta={setMeta}
                            handleSearchEvent={handleSearchEvent}
                            categoriaPago={props.categoriaPago}
                            />
                            <BodyTableReferencias
                            pagos={pagos}
                            categoriaPago={props.categoriaPago}
                            />
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
    const indexDom = document.getElementById('fofoe-wrapper-index');
    if (indexDom) {
        createRoot(indexDom).render(
            <PagoIndex
                categoriaPago={window.CATEGORIA_PAGO}
                pagos={window.PAGOS}
                meta={window.META}
                years={window.YEARS}
            />
        )
    }
})
