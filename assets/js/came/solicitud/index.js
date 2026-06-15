import * as React from 'react'
import ReactDOM from 'react-dom'
import SolicitudCreate from './create';
import SolicitudEdit from './edit';
import SolicitudAccion from "./components/SolicitudAccion";
import SolicitudShow from "./show";
import SolicitudValidaMontos from "./validaMontos";
import Loader from "../../components/Loader/Loader";
import ReactPaginate from 'react-paginate';
import './styles/tables.scss';
import {getSchemeAndHttpHost} from "../../utils";

console.log('hola mundo', getSchemeAndHttpHost('hola'))
console.log('hola mundo', getSchemeAndHttpHost())

const SolicitudIndex = (props) => {

    const [solicitudes, setSolicitudes] = React.useState(props.solicitudes ? props.solicitudes : [])
    const [isLoading, setIsLoading] = React.useState(false)
    const [meta, setMeta] = React.useState(props.meta);
    const [query, setQuery] = React.useState({});

    const handleSearchEvent = () => {
        setIsLoading(true);
        let querystring = '';
        for (const i in query) {
            if(query[i].trim()!== '') {
                querystring += `${i}=${query[i]}&`;
            }
        }

        fetch(`${getSchemeAndHttpHost()}/came/api/solicitud?${querystring}page=${meta.page}&perPage=${meta.perPage}`)
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
                {/*
                <label> &#160;</label>
                <a href={`${getSchemeAndHttpHost()}/came/solicitud/create`} id="btn_solicitud" className={'form-control btn btn-default'}>Agregar
                    Solicitud</a>
                */}
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
                    <div style={{textAlign: 'center', display: (props.solicitudes.length <= 0 ? 'block': 'none'), padding:'80px 0px'}}><h3>No hay ninguna solicitud registrada</h3></div>
                    <div className={'table-responsive'} style={{display: (props.solicitudes.length > 0 ? 'block': 'none')}}>
                        <table className="table table-striped">
                            <thead>
                            <tr>
                                <th>No. de solicitud <br/><input type="text" placeholder={'No. de solicitud'}
                                           onChange={e => {setQuery(Object.assign(query,{no_solicitud: e.target.value})); handleSearchEvent()}}/></th>
                                <th>Institución Educativa <br/> <input type="text" placeholder={'Institución Educativa'}
                                            onChange={e => {setQuery(Object.assign(query,{institucion: e.target.value})); handleSearchEvent()}}/></th>
                                <th className={'date-col'}>Fecha <br/>
                                    <input type="date" placeholder={'Año-mes-día'}
                                           className={'date-input'}
                                                  onChange={e => {setQuery(Object.assign(query,{fecha: e.target.value})); handleSearchEvent()}}/></th>
                                <th>No. de campos clínicos</th>
                                <th>Estado</th>
                                <th> </th>
                            </tr>
                            </thead>
                            <tbody>
                            {solicitudes.map(solicitud => {
                                return (
                                    <tr key={solicitud.id}>
                                        <td><a href={ `${getSchemeAndHttpHost()}/came/solicitud/${solicitud.id}`}>{solicitud.noSolicitud}</a></td>
                                        <td>{solicitud.institucion.nombre}</td>
                                        <td className={'date-col'}>{solicitud.fecha}</td>
                                        <td>
                                            Solicitados: {solicitud.camposClinicosSolicitados} <br/>
                                            Autorizados: {solicitud.camposClinicosAutorizados}
                                        </td>
                                        <td>{solicitud.estatusCameFormatted}</td>
                                        <td><SolicitudAccion solicitud={solicitud}/></td>
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

export default {SolicitudIndex};

document.addEventListener('DOMContentLoaded', () => {
    const indexDom = document.getElementById('solicitudes-table');
    const createDom = document.getElementById('solicitud-wrapper');
    const editDom = document.getElementById('solicitud-edit-wrapper');
    const validateDom = document.getElementById('solicitud-validate-wrapper');
    const showDom = document.getElementById('solicitud-show-wrapper');
    const validaMontosDom = document.getElementById('solicitud-valida-montos-wrapper');

    if (indexDom) {
        ReactDOM.render(
            <SolicitudIndex
                solicitudes={window.SOLICITUDES}
                meta={window.META}
            />, indexDom
        )
    }
    if (createDom) {
        ReactDOM.render(
            <SolicitudCreate
                instituciones={window.INSTITUCIONES}
                unidades={window.UNIDADES}
            />, createDom
        )
    }
    if (editDom) {
        ReactDOM.render(
            <SolicitudEdit
                solicitud={window.SOLICITUD}
                unidades={window.UNIDADES}
                instituciones={window.INSTITUCIONES}
            />, editDom
        )
    }
    if (showDom) {
        ReactDOM.render(
            <SolicitudShow
                solicitud={window.SOLICITUD}
                convenios={window.CONVENIOS}
            />, showDom
        )
    }
    if (validaMontosDom) {
        ReactDOM.render(
            <SolicitudValidaMontos
                solicitud={window.SOLICITUD}
            />, validaMontosDom);
    }
})
