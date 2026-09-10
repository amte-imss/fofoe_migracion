import * as React from 'react'
import ReactDOM from 'react-dom'
import Loader from "../../components/Loader/Loader";
import ReactPaginate from "react-paginate";
import './index.scss';
import {getSchemeAndHttpHost} from "../../utils";

const AccionFofoe = ({solicitud}) => {
    const RegistroFactura  = () => (<a href={`${getSchemeAndHttpHost()}/fofoe/registrar-factura`}>Registrar Factura</a>);
    const ValidarPagoUnico = () => (<a href={`${getSchemeAndHttpHost()}/fofoe/validar-pago`}>Validar Pago</a>);
    const ValidarPagoMultiple = () =>  (<a href={`${getSchemeAndHttpHost()}/fofoe/validar-pago-multiple`}>Validar Pagos</a>);
    let Result1 = () => (<></>);

    if(solicitud.estatus === 'En validación FOFOE' && solicitud.tipoPago === 'Único'){
        Result1 = ValidarPagoUnico;
    }else if(solicitud.estatus === 'En validación FOFOE'){
        Result1 = ValidarPagoMultiple;
    }

    let Result2 = () => (<></>);
    let requireFactura = false;
    solicitud.pagos.map(pago => {
        if(pago.requiereFactura){
            requireFactura = true;
        }
    });

    if(requireFactura){
        Result2 = RegistroFactura;
    }

    return (<>
        <Result1/> <br/> <RegistroFactura/>
    </>);
}

const EstadosPago = ({solicitud}) => {
    return (<ul>
        {solicitud.pagos.map(pago => {
            if(pago.validado){
                return (<li key={pago.id}>Solicitud Pagada</li>)
            }
            return (<li key={pago.id}>Validación Pendiente</li>)
        })}
    </ul>)
}

const Facturas = ({solicitud}) => {
    return (<ul>
        {solicitud.pagos.map(pago => {
            if(pago.requiereFactura){
                if(pago.factura){
                    return (<li key={pago.id}>{pago.factura.folio}</li>)
                }
                return (<li key={pago.id}>Pendiente</li>)
            }
            return (<li key={pago.id}>No Requerida</li>)
        })}
    </ul>);
}

const SolicitudIndex = (props) => {
    const [solicitudes, setSolicitudes] = React.useState(props.solicitudes ? props.solicitudes : [])
    const [isLoading, setIsLoading] = React.useState(false)
    const [meta, setMeta] = React.useState(props.meta);
    const [query, setQuery] = React.useState({});

    const handleSearchEvent = () => {
        setIsLoading(true);
        let querystring = '';
        for (const i in query) {
            querystring += `${i}=${query[i]}&`;
        }

        fetch(`${getSchemeAndHttpHost()}/fofoe/api/solicitud?${querystring}page=${meta.page}&perPage=${meta.perPage}`)
            .then(response => { return response.json()}, error => {console.error(error)})
            .then(json => {setSolicitudes(json.data); setMeta(json.meta)})
            .finally(() => { setIsLoading(false)});

    }

    const showPaginator = () => {
        return meta.total < meta.perPage ? 'none' : 'block';
    }

    return (
        <>
            <Loader show={isLoading}/>
            <div className="col-md-3">

            </div>
            <div className="col-md-2"/>
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
                    <div style={{textAlign: 'center', display: (props.solicitudes.length <= 0 ? 'none': 'none'), padding:'80px 0px'}}><h3>No hay ningún comprobante de solicitud registrado</h3></div>
                    <div className={'table-responsive'} style={{display: (props.solicitudes.length > 0 ? 'block': 'block')}}>
                        <table className="table table-striped table-fofoe">
                            <thead>
                            <tr>
                                <th>
                                    Delegación <br/>
                                </th>
                                <th>
                                    Origen de los Recursos
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
                                <th>
                                    Factura(s)

                                </th>
                                <th>
                                    Fecha(s) Registro(s) de Pago(s)
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
                                <th> </th>
                                <th><input type="text" placeholder={'Institución Educativa'}
                                           onChange={e => {setQuery(Object.assign(query,{institucion: e.target.value})); handleSearchEvent()}}/></th>
                                <th><input type="text" placeholder={'No. de Solicitud'}
                                           onChange={e => {setQuery(Object.assign(query,{no_solicitud: e.target.value})); handleSearchEvent()}}/></th>
                                <th><input type="text" placeholder={'No. de Referencia'}
                                           onChange={e => {setQuery(Object.assign(query,{referencia: e.target.value})); handleSearchEvent()}}/></th>
                                <th><input type="text" placeholder={'Factura'}
                                           onChange={e => {setQuery(Object.assign(query,{factura: e.target.value})); handleSearchEvent()}}/></th>
                                <th> </th>
                                <th> </th>
                                <th> </th>
                            </tr>
                            </thead>
                            <tbody>
                            {solicitudes.map(solicitud => {
                                return (
                                    <tr key={solicitud.id}>
                                        <td>{solicitud.delegacion.nombre}</td>
                                        <td>CCS</td>
                                        <td>{solicitud.institucion.nombre}</td>
                                        <td>{solicitud.noSolicitud}</td>
                                        <td>
                                            <ul>
                                                {solicitud.pagos.map(pago => {
                                                    return (<li key={pago.id}>
                                                        {pago.referenciaBancaria}
                                                    </li>);
                                                })}
                                            </ul>
                                        </td>
                                        <td><Facturas solicitud={solicitud}/></td>
                                        <td>
                                            <ul>
                                                {solicitud.pagos.map(pago => {
                                                    return (<li key={pago.id}>
                                                        {pago.fechaPagoFormatted}
                                                    </li>);
                                                })}
                                            </ul>
                                        </td>
                                        <td><EstadosPago solicitud={solicitud}/></td>
                                        <td>
                                            <AccionFofoe solicitud={solicitud}/>
                                        </td>
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
            <SolicitudIndex
                solicitudes={window.SOLICITUDES}
                meta={window.META}
            />, indexDom
        )
    }
})