import * as React from 'react'
import {dateFormat, getSchemeAndHttpHost, moneyFormat, getEstatusPago, toOrdinalFormat} from "../../utils";
import {ESTATUS_PAGO} from "../../constants";
import {ESTATUS_RESIDENCIA} from "../constants";

const LinkDescargaReferenciaPago = ({residencia, lastPago, setReferenciaDescargada}) => {

  const urlDownload = `${getSchemeAndHttpHost()}/residente/residencia/${residencia.id}/referencia-pago/download`
  const estatus_allowed = [ESTATUS_PAGO.SIN_REGISTRO, ESTATUS_PAGO.INCORRECTO]

  function handleClick() {
    setReferenciaDescargada(true)
  }

  return (
    estatus_allowed.includes(getEstatusPago(lastPago, residencia.pagos.length)) &&
    <a href={urlDownload} onClick={handleClick} className="btn btn-sm btn-block btn-link" target="_blank"  >
      Descargar referencia
    </a>
  )
}

const LinkCargaComprobantePago = ({residencia, lastPago, residenciaDownloaded}) => {

  const urlUpload = `${getSchemeAndHttpHost()}/residente/pagos/${residencia.lastPago.id}/carga-de-comprobante-de-pago`
  const estatus_residencia_allowed = [ESTATUS_RESIDENCIA.FORMATO_PAGO_DESCARGADO, ESTATUS_PAGO.INCORRECTO]
  const estatus_pago_allowed = [ESTATUS_PAGO.SIN_REGISTRO, ESTATUS_PAGO.INCORRECTO]
  const estatus_last_pago =  getEstatusPago(lastPago, residencia.pagos.length)

  return (
    (estatus_residencia_allowed.includes(residencia.estatus) || residenciaDownloaded)
    && estatus_pago_allowed.includes(estatus_last_pago) &&
    <a href={urlUpload} className="btn btn-block btn-sm btn-success" >
      {
        estatus_last_pago === ESTATUS_PAGO.INCORRECTO ?
          'Rectificar comprobante'
          : 'Cargar Comprobante de pago'
      }
    </a>
  )
}

const LinkDescargaComprobantePago = ({residencia, lastPago}) => {

  const urlDownload = `${getSchemeAndHttpHost()}/residente/residencia/${residencia.id}/comprobantes-pago/download`
  const estatus_allowed = [ESTATUS_PAGO.VALIDADO, ESTATUS_PAGO.EN_VALIDACION]

  return (
    estatus_allowed.includes(getEstatusPago(lastPago, residencia.pagos.length)) &&
    <div>
      <a href={urlDownload} className="link"  target="_blank" >
        Descargar comprobante(s) de pago
      </a>
      {
        getEstatusPago(lastPago) === ESTATUS_PAGO.EN_VALIDACION &&
        <p> * Pendiente de validar *</p>
      }
    </div>


  )
}

const Residencias = ({residencias}) => {

  function getLastPago(pagos, lastPagoId) {
    const results =  pagos.filter(item => {
      return item.id === lastPagoId;
    });
    if(results.length > 0){
      return results[0];
    }
  }

  function getFechaPago(pago) {
    if (pago.fechaPago !== null) {
      return dateFormat(pago.fechaPago)
    }

    return 'Pendiente'
  }

  return (
    <div className="row">
      <div className="panel panel-default col-md-12">
        <div className="panel-body">
          <table className='table'>
            <thead className='headers'>
            <tr>
              <th>Ciclo Académico</th>
              <th>Especialidad</th>
              <th>Grado Académico</th>
              <th>Fecha de Pago</th>
              <th>Monto de Pago</th>
              <th>Estatus del Pago</th>
              <th></th>
            </tr>
            </thead>
            <tbody>
            {
              residencias.length !== 0 ?
                residencias.map((residencia, index) => {
                  const lastPago =  getLastPago(residencia.pagos, residencia.lastPago.id)
                  const { useState, useEffect } = React
                  const [ referenciaDescargada, setReferenciaDescargada ] = useState(false)

                  return (
                  <tr key={index}>
                    <td>{residencia.ciclo}</td>
                    <td>{residencia.especialidad}</td>
                    <td>{ toOrdinalFormat(residencia.grado) }</td>
                    <td>{getFechaPago(lastPago)}</td>
                    <td>{ moneyFormat(residencia.monto) } {residencia.tipoMoneda}</td>
                    <td>{ residencia.estatus !== 'Pendiente factura FOFOE' ? getEstatusPago(lastPago, residencia.pagos.length) : 'Pendiente factura FOFOE' }</td>
                    <td>
                      <LinkDescargaReferenciaPago residencia={residencia} lastPago={lastPago} setReferenciaDescargada={setReferenciaDescargada} />
                      <LinkCargaComprobantePago residencia={residencia} lastPago={lastPago} residenciaDownloaded={referenciaDescargada} />
                      <LinkDescargaComprobantePago residencia={residencia} lastPago={lastPago} />
                    </td>
                  </tr>
                )})
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
  )
}

export default Residencias