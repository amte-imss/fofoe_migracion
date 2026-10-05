import React from "react";
import createRoot from "react-dom/client";
import DatosGeneralesResidente from "../../components/DatosGeneralesResidente";
import {
  dateFormat,
  getEstatusPago,
  getLastPago,
  getSchemeAndHttpHost,
  moneyFormat,
  toOrdinalFormat
} from "../../../utils";
import {getMontoTotalResidencia} from "../../utils";
import {ESTATUS_PAGO} from "../../../constants";


const LinkOficioAceptacion = ({residencia}) => {
  const urlDownload = `${getSchemeAndHttpHost()}/posgrado/residencia/${residencia.id}/oficio-aceptacion/download`
  return (
    residencia.oficioAceptacion &&
    <a href={urlDownload} target="_blank">
      Descargar Oficio
    </a>
  )
}

const LinkDescargaComprobantePago = ({residencia, lastPago}) => {

  const urlDownload = `${getSchemeAndHttpHost()}/posgrado/residencia/${residencia.id}/comprobantes-pago/download`
  const estatus_allowed = [ESTATUS_PAGO.VALIDADO, ESTATUS_PAGO.EN_VALIDACION]

  return (
    estatus_allowed.includes(getEstatusPago(lastPago, residencia.pagos.length)) &&
    <div>
      <a href={urlDownload} className="link" >
        Descargar comprobante(s) de pago
      </a>
      {
        getEstatusPago(lastPago) === ESTATUS_PAGO.EN_VALIDACION &&
        <p> * Pendiente de validar *</p>
      }
    </div>


  )
}

const EstatusPago = ({residencia}) => {

  const lastPago = getLastPago(residencia.pagos)

  return (
    <div>
      { residencia.estatus != 'Pendiente factura FOFOE' ? getEstatusPago( lastPago, residencia.pagos.length  ): 'Pendiente factura FOFOE'}
      <LinkDescargaComprobantePago residencia={residencia} lastPago={lastPago} />
    </div>
  )

}

const DatosResidencia = ({residencia}) => {

  return (
    <div>
      <div className={"row mt-20 mb-5"}>
        <div className="col-md-12">
          <h2>Datos Residencia</h2>
        </div>
      </div>
      <div className="row">
        <div className="col-md-4">
          <div className="form-group">
            <div><span>Oficio Aceptación</span></div>
            <div><span>{residencia.folio}</span></div>
            <div><LinkOficioAceptacion residencia={residencia} /></div>
          </div>
        </div>
        <div className="col-md-4">
          <div className="form-group">
            <div><span>Sede</span></div>
            <div><span>{residencia.sede}</span></div>
          </div>
        </div>
        <div className="col-md-4">
          {
            residencia.subsede &&
            <div className="form-group">
              <div><span>Subsede</span></div>
              <div><span>{residencia.subsede}</span></div>
            </div>
          }
        </div>
      </div>
    </div>
  )
}

const ExpedienteResidencias = ({residencias}) => {

  function getFechaPago(pago) {
    if (pago.fechaPago !== null) {
      return dateFormat(pago.fechaPago)
    }

    return 'PENDIENTE'
  }

  return (
    <div>
      <div className={"row mb-10"}>
        <div className="col-md-12">
          <h2>Expediente</h2>
        </div>
      </div>
      <div className="row">
        <div className="panel panel-default col-md-12">
          <div className="panel-body">
            <table className='table'>
              <thead className='headers'>
              <tr>
                <th>Ciclo Académico</th>
                <th>Meses Rotación</th>
                <th>Especialidad</th>
                <th>Grado Académico</th>
                <th>Fecha de Pago</th>
                <th>Monto de Pago</th>
                <th>Estatus del Pago</th>
              </tr>
              </thead>
              <tbody>
              {
                residencias.length !== 0 ?
                  residencias.map((residencia, index) => {
                    const montoResidencia = getMontoTotalResidencia(residencia)
                    return (
                      <tr key={index}>
                        <td>{residencia.ciclo}</td>
                        <td>{dateFormat(residencia.fechaInicio)} - {dateFormat(residencia.fechaTermino)}</td>
                        <td>{residencia.especialidad}</td>
                        <td>{ toOrdinalFormat(residencia.grado) }</td>
                        <td>{ getFechaPago(residencia.pagos[0])}</td>
                        <td>{ moneyFormat( montoResidencia[0] )} { montoResidencia[1] }</td>
                        <td> <EstatusPago residencia={residencia} /> </td>
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
    </div>
  )
}

const DetalleResidente = ({residente}) => {
  return (
    <div>
      <DatosGeneralesResidente  residente={residente} />
      <DatosResidencia residencia={residente.residencias[0]} />
      <ExpedienteResidencias residencias={residente.residencias} />
    </div>
  )
}

document.addEventListener('DOMContentLoaded', () => {
    const root = createRoot.createRoot(document.getElementById('residente-show-wrapper'));
    root.render(
      <DetalleResidente
        residente={window.RESIDENTE_PROP}
      />
    )
  }
);
