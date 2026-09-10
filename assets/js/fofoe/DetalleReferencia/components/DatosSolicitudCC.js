import {TIPO_PAGO} from "../../../constants";
import {getSchemeAndHttpHost, moneyFormat} from "../../../utils";
import * as React from "react";
import AccionFofoe from "./AccionFofoe";

const DatosSolicitudCC = ({solicitud, campos, pago}) => {

  const montoTotal = getMontoTotal()

  function getMontoTotal() {
    if (solicitud.tipoPago === TIPO_PAGO.UNICO) {
      return solicitud.monto;
    }
    return campos.length > 0 ? campos[0].monto : 0;
  }

  function isPagoMultiple() {
    return solicitud.tipoPago === TIPO_PAGO.MULTIPLE;
  }

  function getMontoTotalTitle() {
    return isPagoMultiple() ?
      'Monto total del campo clínico:'
      : 'Monto total de la solicitud:';
  }

  function getEstado() {
    return !isPagoMultiple() ?
      solicitud.estatus
      : (campos.length > 0 ?
        campos[0].estatus.nombre
        : '' )
  }

  return (
    <div className="col-md-12 mb-20">
      <div className="row">
        <div className="col-md-4">
          <p className='mb-5'><strong>Solicitud</strong></p>
          <p className='mb-5'>No. de Solicitud: <strong>{solicitud.noSolicitud}</strong></p>
          <p className='mb-5'>Fecha de registro: <strong>{solicitud.fecha}</strong></p>
          <p className='mb-5'>Tipo de pago: <strong>{solicitud.tipoPago}</strong></p>
          <p className='mb-20'>{getMontoTotalTitle()} <strong>{moneyFormat(montoTotal)}</strong></p>
        </div>
        <div className="col-md-4">
          <p className='mb-5'><strong>Institución</strong></p>
          <p className='mb-5'>Nombre: <strong><a href={`${getSchemeAndHttpHost()}/fofoe/detalle-ie/${solicitud.institucion.id}`}>{solicitud.institucion.nombre}</a></strong></p>
          <p className='mb-5'>RFC: <strong>{solicitud.institucion.rfc}</strong></p>
          <p className='mb-5'>OOAD: <strong>{solicitud.delegacion.nombre}</strong></p>
          {
            campos.length > 0 && campos[0].unidad.esUmae ?
              <p className='mb-5'>UMAE: <strong>{campos[0].unidad.nombre}</strong></p>
              : null
          }
        </div>
      </div>
      <div className="row">
        <div className="col-md-6 mt-10">
          <p>Referencia Bancaria: <strong>{pago.referenciaBancaria}</strong></p>
          <p><strong>Estado de la referencia:</strong> {getEstado()}</p>
        </div>
        <AccionFofoe pago={pago} />
      </div>
    </div>
  )
}

const DatosCampo = ({campo}) => {
  return (
    <div className="col-md-4" >
      <p className='mb-5'><strong>Campo clínico</strong></p>
      <p className='mb-5'>Sede: <strong>{campo.unidad.nombre}</strong></p>
      <p className='mb-5'>Carrera: <strong>{campo.displayCarrera}</strong></p>
      <p className='mb-5'>Período: <strong>{campo.displayFechaInicial} - {campo.displayFechaFinal}</strong></p>
    </div>
  )
}

export default DatosSolicitudCC