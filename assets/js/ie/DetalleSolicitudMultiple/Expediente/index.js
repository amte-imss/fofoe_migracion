import * as React from 'react'
import {getSchemeAndHttpHost} from "../../../utils";
import {SOLICITUD} from "../../../constants";
const DEFAULT_DOCUMENT_VALUE = '-'

const Expediente = ({ solicitud }) => {

  function getUniqCamposClinicos(comprobantesPago) {
    const result = [];
    const map = new Map();
    for (const item of comprobantesPago) {
      if(!map.has(item.options.campoClinicoId)){
        map.set(item.options.campoClinicoId, true);
        result.push({
          campoClinicoId: item.options.campoClinicoId,
          referenciaBancaria: item.options.referenciaBancaria,
          unidad: item.options.unidad
        });
      }
    }

    return result;
  }

  function showReferenciaBancariaExpediente() {
    let estatusValidos = [SOLICITUD.CARGANDO_COMPROBANTES, SOLICITUD.CREDENCIALES_GENERADAS, SOLICITUD.EN_VALIDACION_FOFOE]
    return estatusValidos.includes(solicitud.estatus)
  }

  function isPagoDelCampoClinico(comprobante, item) {
    return comprobante.options.campoClinicoId === item.campoClinicoId;
  }

  return(
    <div className='panel panel-default'>
      <div className='panel-body'>
        <table className='table'>
          <thead>
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
              DEFAULT_DOCUMENT_VALUE}</td>
            <td>{solicitud.expediente.oficioMontos.fecha || DEFAULT_DOCUMENT_VALUE}</td>

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
            getUniqCamposClinicos(solicitud.expediente.comprobantesPago).map((item, index) => (
              <tr key={index}>
                <td>Comprobante de pago del campo clínico {item.unidad} con <strong>No. de referencia {item.referenciaBancaria}</strong></td>
                <td colSpan="3">
                  <table className='table table-nested'>
                    <tbody>
                    {
                      solicitud.expediente.comprobantesPago.map((comprobante, key) => {
                        return(
                          <tr key={key}>
                            {
                              isPagoDelCampoClinico(comprobante, item) &&
                              <>
                                <td className='col-md-10'>
                                  <span
                                    key={key}
                                  >
                                    {comprobante.descripcion}
                                    <br/>
                                  </span>
                                </td>
                                <td>
                                  <span
                                    key={key}
                                  >
                                    {comprobante.fecha}
                                    <br/>
                                  </span>
                                </td>
                                <td>
                                  <a
                                    key={key}
                                    href={`${getSchemeAndHttpHost()}/ie/pagos/${comprobante.options.pagoId}/descargar-comprobante-de-pago`}
                                    target='_blank' download
                                  >
                                    Descargar <br/>
                                  </a>
                                </td>
                              </>
                            }
                          </tr>
                        )
                      })
                    }
                    </tbody>
                  </table>
                </td>
              </tr>
            ))
          }
          </tbody>
        </table>
      </div>
    </div>
  )
}

export default Expediente
