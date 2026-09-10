import * as React from 'react'
import ReactDOM from 'react-dom'
import Loader from "../../components/Loader/Loader";
import ReactPaginate from "react-paginate";
import './index.scss';
import {getSchemeAndHttpHost} from "../../utils";

const numberFormat = new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
})

const AccionFofoe = ({pago}) => {
    const RegistroFactura  = () => (<a href={`${getSchemeAndHttpHost()}/fofoe/pagos/${pago.id}/registrar-factura`}>Registrar Factura</a>);
    const ValidarPago = () => (<a href={`${getSchemeAndHttpHost()}/fofoe/pagos/${pago.id}/validacion-de-pago`}>Validar Pago</a>);
    if(pago.validado != null && pago.validado && pago.requiereFactura && !pago.factura){
        return (<RegistroFactura/>);
    }else if(pago.validado == null){
        return (<ValidarPago/>);
    }else{
        return (<></>);
    }
}

const EstadosPago = ({pago}) => {
    if(pago.validado == null){
        return (<span>Pendiente Validación</span>);
    }else if((pago.validado && !pago.requiereFactura) || (pago.validado && pago.requiereFactura && pago.factura)){
        return (<span>Solicitud Pagada</span>);
    }else if(pago.validado && pago.requiereFactura && !pago.factura){
        return (<span>Factura Pendiente</span>);
    }else{
        return (<span>Pago no Válido</span>);
    }
}

const Facturas = ({pago}) => {
    if(pago.requiereFactura && !pago.factura){
        return (<span>Pendiente</span>);
    }else if(pago.factura){
        return (<a href={`${getSchemeAndHttpHost()}/factura/${pago.factura.id}/download`}>{pago.factura.folio}</a>);
    }else{
        return (<span>No Requerida</span>);
    }
}

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

        fetch(`${getSchemeAndHttpHost()}/fofoe/api/pago?${querystring}page=${meta.page}&perPage=${meta.perPage}`)
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
                            onChange={e => {setQuery(Object.assign(query, {year: e.target.value}));  handleSearchEvent(); }}>
                        {props.years.map(item => {
                            return (<option key={`${item.year}`} value={`${item.year}`}>{item.year}</option>)
                        })}
                    </select>
                    <span className="help-block"> </span>
                </div>
            </div>
            <div className="col-md-3">
                <div className={``}>
                    <label htmlFor="orderby">Ordenar por: </label>
                    <select id="orderby" className="form-control"
                            onChange={e => {setQuery(Object.assign(query, {orderby: e.target.value}));  handleSearchEvent(); }}>
                        {/*<option value="1">1</option>*/}
                        <option value="a">Fecha de pago: más reciente</option>
                        <option value="b">Fecha de pago: más antigua</option>
                        <option value="c">Número de solicitud: de mayor a menor</option>
                        <option value="d">Número de solicitud: de menor a mayor</option>
                    </select>
                    <span className="help-block"> </span>
                </div>
            </div>
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
                    <div className={'table-responsive'} style={{display: (props.pagos.length > 0 ? 'block': 'none')}}>
                        <table className="table table-striped table-fofoe">
                            <thead>
                            <tr>
                                <th>
                                    Delegación <br/>
                                </th>
                                <th>
                                    Institución Educativa
                                </th>
                                <th>
                                    No. de Solicitud

                                </th>
                                <th>
                                    No. de Referencia

                                </th>
                                <th>Monto</th>
                                <th>
                                    Factura
                                </th>
                                <th>
                                    Fecha Registro de Pago
                                </th>
                                <th>
                                    Estado
                                </th>
                                <th>

                                </th>
                            </tr>
                            <tr style={{background: 'lightgray'}}>
                                <th>
                                    <input type="text" placeholder={'Delegación'}
                                           onChange={e => {setQuery(Object.assign(query,{delegacion: e.target.value})); handleSearchEvent()}}/>
                                </th>
                                <th><input type="text" placeholder={'Institución Educativa'}
                                           onChange={e => {setQuery(Object.assign(query,{institucion: e.target.value})); handleSearchEvent()}}/></th>
                                <th><input type="text" placeholder={'No. de Solicitud'}
                                           onChange={e => {setQuery(Object.assign(query,{no_solicitud: e.target.value})); handleSearchEvent()}}/></th>
                                <th><input type="text" placeholder={'No. de Referencia'}
                                           onChange={e => {setQuery(Object.assign(query,{referencia: e.target.value})); handleSearchEvent()}}/></th>
                                <th><input type="number" placeholder={'Monto'}
                                           onChange={e => {setQuery(Object.assign(query,{monto: e.target.value})); handleSearchEvent()}}/></th>
                                <th><input type="text" placeholder={'Folio Factura'}
                                           onChange={e => {setQuery(Object.assign(query,{factura: e.target.value})); handleSearchEvent()}}/></th>
                                <th> </th>
                                <th>
                                    <select className="form-control"
                                            onChange={e => {console.log(e.target.value); setQuery(Object.assign(query, {estado: e.target.value}));  handleSearchEvent(); }}>>
                                        <option value="">Seleccionar ...</option>
                                        <option value="a">Pendiente Validación</option>
                                        <option value="b">Solicitud Pagada</option>
                                        <option value="d">Pago no Válido</option>
                                        <option value="c">Factura Pendiente</option>
                                    </select>
                                </th>
                                <th> </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr style={{textAlign: 'center', display: (pagos.length <= 0 ? 'table-row': 'none'), padding:'80px 0px'}}>
                                <td colSpan={9}>
                                    <h3>No se encontró ningún registro.</h3>
                                </td>
                            </tr>
                            {pagos.map(pago => {
                                return (
                                    <tr key={pago.id}>
                                        <td>{pago.solicitud.delegacion.nombre}</td>
                                        <td>{pago.solicitud.institucion.nombre}</td>
                                        <td>{pago.solicitud.noSolicitud}</td>
                                        <td>{pago.referenciaBancaria}</td>
                                        <td>$ {numberFormat.format(pago.monto.toString())}</td>
                                        <td><Facturas pago={pago}/></td>
                                        <td>{pago.fechaPagoFormatted}</td>
                                        <td><EstadosPago pago={pago}/></td>
                                        <td><AccionFofoe pago={pago} /></td>
                                    </tr>
                                )
                            })}
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
                                pageCount={meta.total / meta.perPage}
                                marginPagesDisplayed={2}
                                pageRangeDisplayed={parseInt(meta.perPage)}
                                onPageChange={value => {console.log(value); setMeta(Object.assign(meta, {page:value.selected + 1})); handleSearchEvent(query)}}
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
        ReactDOM.render(
            <PagoIndex
                pagos={window.PAGOS}
                meta={window.META}
                years={window.YEARS}
            />, indexDom
        )
    }
})