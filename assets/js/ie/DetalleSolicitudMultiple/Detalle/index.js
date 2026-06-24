import * as React from 'react'
import { CAMPO_CLINICO, SOLICITUD } from "../../../constants";
import {getActionNameByInstitucionEducativa, getSchemeAndHttpHost} from "../../../utils";

const DetalleSolicitudMultiple = ({ solicitud }) => {

  function getFactura(facturaId, requiereFactura) {
    if(requiereFactura === false) return 'No solicitada';
    if(!facturaId) return 'Pendiente';

    return(
      <a
        href={`${getSchemeAndHttpHost()}/ie/factura/${facturaId}/download`}
        target='_blank'
        download
      >Descargar</a>
    )
  }

  function handleDownloadReferencias() {
    const route = `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/descargar-referencias-bancarias`;
    window.open(route);
    window.location.reload();
  }

  function isCampoClinicoAutorizado(lugaresAutorizados) {
    return lugaresAutorizados > 0;
  }

  function getActionButton(campoClinico) {
    if(!isCampoClinicoAutorizado(campoClinico.lugaresAutorizados)) return;

    if(campoClinico.estatus === CAMPO_CLINICO.PAGO) {
      return(
        <button className='btn btn-default' disabled={true}>En validación por FOFOE</button>
      );
    }
    else if(campoClinico.estatus === CAMPO_CLINICO.PAGO_NO_VALIDO) {
      return(
        <a
          href={`${getSchemeAndHttpHost()}/ie/pagos/${campoClinico.pago.id}/carga-de-comprobante-de-pago`}
          className='btn btn-default'
        >
          Corregir comprobante
        </a>
      );
    }
    else if(campoClinico.estatus  === CAMPO_CLINICO.CREDENCIALES_GENERADAS
    || campoClinico.estatus  === CAMPO_CLINICO.PENDIENTE_FACTURA_FOFOE ) {
      return('Pago validado')
    }

    return solicitud.estatus === SOLICITUD.CARGANDO_COMPROBANTES ?
      <a
        href={`${getSchemeAndHttpHost()}/ie/pagos/${campoClinico.pago.id}/carga-de-comprobante-de-pago`}
        className="btn btn-success"
      >
        Cargar comprobante
      </a> :
        'Pendiente'
      ;
  }

  function getDelegacion() {
    return solicitud.camposClinicos.length > 0 ?
      solicitud.camposClinicos[0].unidad.delegacion
      : '';
  }

  function getUmae() {
    return esUmae() ?
      solicitud.camposClinicos[0].unidad.nombre
      : '';
  }

  function esUmae() {
    //console.log(solicitud.camposClinicos)
    //console.log(solicitud.camposClinicos[0].unidad.esUmae)
    return solicitud.camposClinicos.length > 0 ?
      solicitud.camposClinicos[0].unidad.esUmae
      : false;
  }

  return(
    <div className='row'>
      <div className="col-md-12">
        <p><span className="text-bold">No. solicitud:</span> {solicitud.noSolicitud}</p>
        <p><strong>OOAD:</strong> {getDelegacion()}</p>
        {esUmae() ?
          <p><strong>UMAE:</strong> {getUmae()}</p>
          : null
        }
        <div className="row">
          <div className="col-md-6 mt-10">
            <p className="mb-20"><span className="text-bold">Estado de la solicitud:</span> {solicitud.estatus}</p>
          </div>
          {
            solicitud.estatus === SOLICITUD.FORMATOS_DE_PAGO_GENERADOS &&
            <div className="col-md-6">
              <strong>Acción</strong>&nbsp;
              <a
                  href="" onClick={handleDownloadReferencias}
                className='btn btn-default'
              >
                {getActionNameByInstitucionEducativa(solicitud.estatus, false)}
              </a>
            </div>
          }
        </div>
      </div>
      <div className="col-md-12 mt-20">
        <p><span className="text-bold">Se autorizarón:</span> {solicitud.totalCamposClinicosAutorizados} de {solicitud.camposClinicos.length} campos clínicos solicitados</p>
      </div>
      <div className="col-md-12 mt-10">
        <div className="panel panel-default">
          <div className="panel-body">
            <table className='table'>
              <thead className='headers'>
              <tr>
                <th>Sede</th>
                <th>Campo clínico</th>
                <th>Carrera</th>
                <th>No. lugares</th>
                <th>Periodo</th>
                <th>Estado</th>
                <th>Referencia</th>
                <th>Comprobante</th>
                <th>Factura</th>
              </tr>
              </thead>
              <tbody>
              {
                solicitud.camposClinicos.map((campoClinico, index) =>
                  <tr key={index} className={campoClinico.lugaresAutorizados !== null && campoClinico.lugaresAutorizados <= 0 ? 'bg-danger' : ''}>
                    <td>{campoClinico.unidad.nombre}</td>
                    <td>{campoClinico.convenio.cicloAcademico.nombre}</td>
                    <td>{campoClinico.convenio.carrera.nivelAcademico.nombre} - {campoClinico.convenio.carrera.nombre}
                      <br />
                      Asignatura: {campoClinico.asignatura ?? '_'}
                    </td>
                    <td>Solicitados {campoClinico.lugaresSolicitados}
                      <br />
                      Autorizados {campoClinico.lugaresAutorizados}
                      <br />
                      {
                        parseInt(campoClinico.totalTrabajadoresBecados) > 0 ?
                          `Trabajadores Becados ${campoClinico.totalTrabajadoresBecados}`
                          : ''
                      }
                    </td>
                    <td>{campoClinico.fechaInicial} <br /> - <br /> {campoClinico.fechaFinal}
                      <br />
                      Horario: {campoClinico.horario ?? 'Sin asignar'}
                    </td>
                    <td>
                      {
                        isCampoClinicoAutorizado(campoClinico.lugaresAutorizados) &&
                        getStatus(campoClinico.estatus)
                      }
                    </td>
                    <td>{campoClinico.referenciaBancaria}</td>
                    <td>
                      {getActionButton(campoClinico)}
                    </td>
                    <td>
                      {
                        isCampoClinicoAutorizado(campoClinico.lugaresAutorizados) &&
                        getFactura(campoClinico.pago.facturaId, campoClinico.pago.requiereFactura)
                      }
                    </td>
                  </tr>
                )
              }
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  )
}

function getStatus(status){
    if(status === CAMPO_CLINICO.CREDENCIALES_GENERADAS){
        return 'Pago';
    }
    return status;
}
export default DetalleSolicitudMultiple
