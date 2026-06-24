import * as React from 'react'
import { solicitudesGet } from "../../api/solicitud";
import { TIPO_PAGO, SOLICITUD } from "../../../constants";
import {
  getActionNameByInstitucionEducativa, getSchemeAndHttpHost,
  isActionDisabledByInstitucionEducativa
} from "../../../utils";
import FiltroTablaSolicitudes from "./FiltroTablaSolicitudes";
import PaginadorTablaSolicitudes from "./PaginadorTablaSolicitudes";
const DEFAULT_PAGE = 1;
const DEFAULT_STRING_VALUE = '';

const PER_PAGE_DEFAULT_SELECT_VALUES = [];
PER_PAGE_DEFAULT_SELECT_VALUES.FIRST_OPTION = 5;
PER_PAGE_DEFAULT_SELECT_VALUES.SECOND_OPTION = 10;
PER_PAGE_DEFAULT_SELECT_VALUES.THIRD_OPTION = 15;

const MisSolicitudes = () => {
  const { useState, useEffect } = React
  const [ camposClinicos, setCamposClinicos ] = useState([])
  const [ search, setSearch ] = useState(DEFAULT_STRING_VALUE)
  const [ tipoPago, setTipoPago ] = useState(DEFAULT_STRING_VALUE)
  const [ estatus, setEstatus ] = useState(DEFAULT_STRING_VALUE)
  const [ orderBy, setOrderBy ] = useState(DEFAULT_STRING_VALUE)
  const [ currentPage, setCurrentPage ] = useState(DEFAULT_PAGE)
  const [ perPage, setPerPage ] = useState(PER_PAGE_DEFAULT_SELECT_VALUES.FIRST_OPTION)
  const [ isLoading, toggleLoading ] = useState(false)
  const [ pagination, setPagination ] = useState({
    pageCount: 0,
    totalCount: 0,
    firstItemNumber: 0,
    lastItemNumber: 0
  })

  function isRequestAllowed() {
    return currentPage !== null ||
      tipoPago !== DEFAULT_STRING_VALUE ||
      search !== DEFAULT_STRING_VALUE ||
      PER_PAGE_DEFAULT_SELECT_VALUES.includes(perPage) ||
      orderBy !== DEFAULT_STRING_VALUE ||
      estatus !== DEFAULT_STRING_VALUE
  }

  useEffect(() => {
    if(isRequestAllowed()) getCamposClinicos();
  }, [currentPage, tipoPago, search, perPage, orderBy, estatus])

  function handleSearch() {
    if(!search) return;
    getCamposClinicos();
  }

  function getCamposClinicos() {
    toggleLoading(true);

    solicitudesGet(
      tipoPago,
      estatus,
      currentPage,
      perPage,
      orderBy,
      search
    ).then((res) => {
        setCamposClinicos(res.camposClinicos)
        setPagination({
          pageCount: res.paginationData.pageCount,
          totalCount: res.paginationData.totalCount,
          firstItemNumber: res.paginationData.firstItemNumber,
          lastItemNumber: res.paginationData.lastItemNumber
        })
      })
      .finally(() => toggleLoading(false))
  }

  function handleStatusAction(solicitud, showDetalle=false) {
    if(isActionDisabledByInstitucionEducativa(solicitud.estatus) && !isSolicitudMultipleYDentroDeLosEstatus(solicitud)) return;

    let redirectRoute = ''

    switch(solicitud.estatus) {
      case SOLICITUD.CREADA:
        redirectRoute = `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/editar`
        break;
      case SOLICITUD.CONFIRMADA:
        redirectRoute = !showDetalle ? `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/registrar-montos`
          : `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/detalle-de-solicitud`
        break
      case SOLICITUD.MONTOS_INCORRECTOS_CAME:
        redirectRoute = `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/corregir-montos`
        break
      case SOLICITUD.MONTOS_VALIDADOS_CAME:
        redirectRoute = `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/seleccionar-forma-de-pago`
        break
      case SOLICITUD.FORMATOS_DE_PAGO_GENERADOS:
        //redirectRoute = `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/detalle-de-forma-de-pago`
        //break
        return handleDownloadReferencias(solicitud)
      case SOLICITUD.CREDENCIALES_GENERADAS:
        redirectRoute = isPagoMultiple(solicitud) ?
          `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/detalle-de-solicitud-multiple` :
          `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/detalle-de-solicitud`
        break
      case SOLICITUD.CARGANDO_COMPROBANTES:
      case SOLICITUD.EN_VALIDACION_FOFOE:
        if(isPagoMultiple(solicitud)) redirectRoute = `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/detalle-de-solicitud-multiple`
        else redirectRoute = `${getSchemeAndHttpHost()}/ie/pagos/${solicitud.ultimoPago}/carga-de-comprobante-de-pago`
    }

    window.location.href = redirectRoute
  }

  function handleDownloadReferencias(solicitud) {
    const route = `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/descargar-referencias-bancarias`;
    window.open(route);
    handleNoSolicitud(null, solicitud)
  }

  function cleanFilters() {
    setTipoPago(DEFAULT_STRING_VALUE)
    setSearch(DEFAULT_STRING_VALUE)
    setEstatus(DEFAULT_STRING_VALUE)
    setCurrentPage(DEFAULT_PAGE)
    setOrderBy(DEFAULT_STRING_VALUE)
    setPerPage(parseInt(PER_PAGE_DEFAULT_SELECT_VALUES.FIRST_OPTION))
  }

  function handleNoSolicitud(event, solicitud) {
    if (event) {
      event.preventDefault();
    }

    window.location = isPagoMultiple(solicitud) ?
      `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/detalle-de-solicitud-multiple` :
      `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/detalle-de-solicitud`
  }

  function isPagoMultiple(solicitud) {
    return solicitud.tipoPago === TIPO_PAGO.MULTIPLE;
  }

  function isSolicitudMultipleYDentroDeLosEstatus(solicitud) {
    return isPagoMultiple(solicitud) && (
      solicitud.estatus === SOLICITUD.EN_VALIDACION_FOFOE
//      || solicitud.estatus === SOLICITUD.CREDENCIALES_GENERADAS
    );
  }

  function handleDisabledActionBySolicitudMultiple(solicitud) {
    if(isSolicitudMultipleYDentroDeLosEstatus(solicitud)) return false;
    return isActionDisabledByInstitucionEducativa(solicitud.estatus);
  }

  return(
    <div>
      <FiltroTablaSolicitudes
        setTipoPago={setTipoPago}
        tipoPago={tipoPago}
        setEstatus={setEstatus}
        estatus={estatus}
        setOrderBy={setOrderBy}
        orderBy={orderBy}
        setSearch={setSearch}
        search={search}
        handleSearch={handleSearch}
        cleanFilters={cleanFilters}
      />
      <div className="row">
        <div className="panel panel-default col-md-12">
          <div className="panel-body">
            <table className='table'>
              <thead className='headers'>
              <tr>
                <th>No. de solicitud</th>
                <th>Fecha de solicitud</th>
                <th> OOAD / UMAE </th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
              </thead>
              <tbody>
              {
                isLoading ?
                  <tr>
                    <th className='text-center' colSpan={7}>Cargando información...</th>
                  </tr> :
                  camposClinicos.length !== 0 ?
                    camposClinicos.map((solicitud, index) => (
                      <tr key={index}>
                        <th>
                          <a
                            href="#"
                            onClick={event => handleNoSolicitud(event, solicitud)}
                          >{solicitud.noSolicitud}</a>
                        </th>
                        <th>{solicitud.fecha}</th>
                        <th>{solicitud.displayDelegacionUmae}</th>
                        <th>{solicitud.estatusIEFormatted}</th>
                        <th>
                          <button
                            className='btn btn-default'
                            disabled={handleDisabledActionBySolicitudMultiple(solicitud)}
                            onClick={() => handleStatusAction(solicitud, true)}
                          >
                            {getActionNameByInstitucionEducativa(solicitud.estatus, solicitud.tipoPago, false)}
                          </button>
                        </th>
                      </tr>
                    )) :
                    <tr>
                      <th className='text-center' colSpan={7}>No hay registros disponibles</th>
                    </tr>
              }
              </tbody>
            </table>
          </div>
          {
            pagination.totalCount > 0 ?
              <p className='text-center'>Mostrando {pagination.firstItemNumber}-{pagination.lastItemNumber} de {pagination.totalCount}</p>
              : null
          }
        </div>
      </div>
      <PaginadorTablaSolicitudes
        pagination={pagination}
        setCurrentPage={setCurrentPage}
        currentPage={currentPage}
        setPerPage={setPerPage}
        perPage={perPage}
        DEFAULT_PAGE={DEFAULT_PAGE}
        PER_PAGE_DEFAULT_SELECT_VALUES={PER_PAGE_DEFAULT_SELECT_VALUES}
      />
    </div>
  )
}

export default MisSolicitudes
