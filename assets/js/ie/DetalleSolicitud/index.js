import React, {Fragment} from 'react';
import ReactDOM from 'react-dom'
import {
  getActionNameByInstitucionEducativa, getSchemeAndHttpHost,
  isActionDisabledByInstitucionEducativa
} from "../../utils"
import { SOLICITUD } from "../../constants"
const DEFAULT_DOCUMENT_VALUE = '-'
const DEFAULT_DOCUMENT = 'Archivo pendiente de carga'

const ListaCampos = ({ solicitud }) => {
  function handleStatusAction() {
    if (isActionDisabledByInstitucionEducativa(solicitud.estatus)) return;

    let redirectRoute = ''
    switch (solicitud.estatus) {
      case SOLICITUD.CONFIRMADA:
      case SOLICITUD.REVISADA:
        redirectRoute = `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/registrar-montos`
        break
      case SOLICITUD.MONTOS_INCORRECTOS_CAME:
        redirectRoute = `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/corregir-montos`
        break
      case SOLICITUD.CARGANDO_COMPROBANTES:
        redirectRoute = `${getSchemeAndHttpHost()}/ie/pagos/${solicitud.ultimoPago.id}/carga-de-comprobante-de-pago`
        break
      case SOLICITUD.MONTOS_VALIDADOS:
      case SOLICITUD.MONTOS_VALIDADOS_CAME:
        redirectRoute = `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/seleccionar-forma-de-pago`
        break
      case SOLICITUD.FORMATOS_DE_PAGO_GENERADOS:
        return handleDownloadReferencias()
    }

    window.location.href = redirectRoute
  }

  console.log(solicitud);

  function handleDownloadReferencias() {
    const route = `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/descargar-referencias-bancarias`;
    window.open(route);
    window.location.reload();
  }

  function showReferenciaBancariaExpediente() {
    let estatusValidos = [SOLICITUD.CARGANDO_COMPROBANTES, SOLICITUD.CREDENCIALES_GENERADAS, SOLICITUD.EN_VALIDACION_FOFOE]
    return estatusValidos.includes(solicitud.estatus)
  }

  function isComprobantesPagoEmpty() {
    return solicitud.expediente.comprobantesPago.length === 0;
  }
  function isFacturasEmpty() {
    return solicitud.expediente.facturas.length === 0;
  }

  function getTotalCamposClinicos() {
    return solicitud.camposClinicos.length;
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
    return solicitud.camposClinicos.length > 0 ?
      solicitud.camposClinicos[0].unidad.esUmae
      : false;
  }

  const estatusSolicitudNoConfirmada = [SOLICITUD.CREADA, SOLICITUD.REGISTRADA]
  const estatusSolicitudConfirmada = [SOLICITUD.CONFIRMADA, SOLICITUD.REVISADA]

  return (
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
            <p><strong>Estado de la solicitud:</strong> {solicitud.estatus}</p>
          </div>
          <div className="col-md-6">
            {
              solicitud.totalCamposClinicosAutorizados > 0
              && ![SOLICITUD.CREDENCIALES_GENERADAS, SOLICITUD.PAGADA].includes(solicitud.estatus) ?
                <>
                  <strong>Acción</strong>&nbsp;
                  <button
                    className='btn btn-default'
                    disabled={isActionDisabledByInstitucionEducativa(solicitud.estatus)}
                    onClick={handleStatusAction}
                  >
                    {getActionNameByInstitucionEducativa(solicitud.estatus, false)}
                  </button>
                </>
                : null
            }
          </div>
        </div>
      </div>
      <div className="col-md-12 mt-20">
        {
          estatusSolicitudNoConfirmada.includes(solicitud.estatus) ?
            <p>Total de campos solicitados: {getTotalCamposClinicos()}</p>
            :
            <p>Se autorizaron {solicitud.totalCamposClinicosAutorizados} de {getTotalCamposClinicos()} campos clínicos</p>
        }
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
                <th>No. de semanas</th>
              </tr>
              </thead>
              <tbody>
              {
                solicitud.camposClinicos.map((campoClinico, index) =>
                    <Fragment key={index}>
                      <tr key={index} className={campoClinico.lugaresAutorizados !== null && campoClinico.lugaresAutorizados <= 0 ? 'bg-danger' : ''}>
                        <td>{campoClinico.unidad.nombre ? campoClinico.unidad.nombre : 'No asignado'}</td>
                        <td>{campoClinico.convenio.cicloAcademico ? campoClinico.convenio.cicloAcademico.nombre : 'No asignado'}</td>
                        <td>{campoClinico.convenio.carrera.nivelAcademico.nombre} - {campoClinico.convenio.carrera.nombre}
                          <br />
                           {campoClinico.asignatura ? `Asignatura: ${campoClinico.asignatura}` :  ''}
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
                        <td>{campoClinico.noSemanas}</td>
                      </tr>
                      { campoClinico.obsValRegistro && estatusSolicitudConfirmada.includes(solicitud.estatus)  ?
                        <tr className={campoClinico.lugaresAutorizados !== null && campoClinico.lugaresAutorizados <= 0 ? 'bg-danger' : ''} >
                        <td colSpan={6}  style={ {borderTop : 'none'} } >
                          <strong>Observaciones</strong>
                        <p className='background' > {campoClinico.obsValRegistro} </p>
                        </td>
                        </tr> : null
                      }
                  </Fragment>
                )
              }
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div className="col-md-12">
        <p className="text-bold mt-10 mb-10">Expediente</p>
        <div className="panel panel-default">
          <div className="panel-body">
            <table className='table'>
              <thead className='headers'>
              <tr>
                <th className='col-md-3'>Documento</th>
                <th className='col-md-7'>Descripción</th>
                <th className='col-md-1'>Fecha</th>
                <th className='col-md-1'>Archivo</th>
              </tr>
              </thead>
              <tbody>
              <tr>
                <td>{solicitud.expediente.oficioMontos.nombre}</td>
                <td>{solicitud.expediente.oficioMontos.descripcion ?
                  solicitud.expediente.oficioMontos.descripcion.split('\n').map((str, index)=><p key={index}>{str}</p>) :
                DEFAULT_DOCUMENT}
                </td>
                <td>{solicitud.expediente.oficioMontos.descripcion ? solicitud.expediente.oficioMontos.fecha : DEFAULT_DOCUMENT_VALUE}</td>
                <td>
                  {
                    solicitud.expediente.oficioMontos.urlArchivo ?
                      <a
                        href={`${getSchemeAndHttpHost()}/ie/solicitud/${solicitud.id}/descargar-comprobante-inscripcion`}
                        target='_blank'
                        download
                      >
                        Descargar
                      </a>
                      :
                      DEFAULT_DOCUMENT_VALUE
                  }
                </td>
              </tr>
              {
                solicitud.expediente.formatosFofoe &&
                <tr>
                  <td>{solicitud.expediente.formatosFofoe.nombre}</td>
                  <td>{solicitud.expediente.formatosFofoe.descripcion}</td>
                  <td>{solicitud.expediente.formatosFofoe.fecha}</td>
                  <td>
                    <a
                        href={`${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/descargar-formatos-fofoe`}
                        target='_blank'
                        download
                    >
                      Descargar
                    </a>
                  </td>
                </tr>
              }
              { solicitud.expediente.formatosFofoe && showReferenciaBancariaExpediente() &&
                <tr>
                  <td>Referencia de pago</td>
                  <td>Archivo ZIP con el formato con la referencia bancaria para el pago</td>
                  <td> - </td>
                  <td>
                  <a
                  href={`${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud.id}/descargar-referencias-bancarias`}
                  target='_blank'
                  download
                  >
                  Descargar
                  </a>
                  </td>
                </tr>
              }
              {
                !isComprobantesPagoEmpty() &&
                <tr>
                  <td>{solicitud.expediente.comprobantesPago[0].nombre} con <strong>No. de referencia {solicitud.expediente.comprobantesPago[0].options.referenciaBancaria}</strong></td>
                  <td colSpan='3'>
                    <table className='table table-nested'>
                      <tbody>
                      {
                        solicitud.expediente.comprobantesPago.map((comprobantePago, index) =>
                          <tr key={index}>
                            <td className='col-md-10'>
                              {comprobantePago.descripcion || DEFAULT_DOCUMENT_VALUE}
                            </td>
                            <td>
                              <p key={index}>{comprobantePago.fecha}</p>
                            </td>
                            <td>
                              <a
                                href={`${getSchemeAndHttpHost()}/ie/pagos/${comprobantePago.options.pagoId}/descargar-comprobante-de-pago`}
                                target='_blank'
                                download
                              >
                                Descargar
                              </a>
                            </td>
                          </tr>
                        )
                      }
                      </tbody>
                    </table>
                  </td>
                </tr>
              }
              {
                !isFacturasEmpty() &&
                <tr>
                  <td>{solicitud.expediente.facturas[0].nombre}</td>
                  <td>{solicitud.expediente.facturas[0].descripcion || DEFAULT_DOCUMENT_VALUE}</td>
                  <td>
                    {
                      solicitud.expediente.facturas.map((factura, index) =>
                        <p key={index}>{factura.fecha}</p>
                      )
                    }
                  </td>
                  <td>
                    {
                      solicitud.expediente.facturas.map((factura, index) =>
                        <p key={index}>
                          <a href={`${getSchemeAndHttpHost()}/ie/factura/${factura.options.facturaId}/download`}
                             target='_blank'
                             download
                          >
                            Descargar
                          </a>
                        </p>
                      )
                    }
                  </td>
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

ReactDOM.render(
  <ListaCampos
    solicitud={window.SOLICITUD_PROP}
    total={window.TOTAL_PROP}
    autorizado={window.AUTORIZADO_PROP}
    campos={window.CAMPOS_PROP}
    pago={window.PAGO_PROP}
  />,
  document.getElementById('detalle-solicitud-component')
);
