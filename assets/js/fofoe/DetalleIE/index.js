import { createRoot } from "react-dom/client";
import * as React from 'react'
import ListConvenios from "../../components/ListConvenios";
import {dateFormat, getSchemeAndHttpHost} from "../../utils";


const numberFormat = new Intl.NumberFormat('es-MX', {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2
})
const ValidarInfo = (
  {
    institucion,
    errores,
    pagos
  }) => {

  return(
    <div

    >
    <label className="mb-10">Información Institución</label>

      <div className='row'>
        <div className='col-md-12'>
          <div className={`form-group ${errores.razonSocial ? 'has-error has-feedback' : ''}`}>
            <label htmlFor="institucion_rfc">Razón Social</label>
            <input
              className='form-control'
              type="text"
              name="institucion[razonSocial]"
              id="institucion_razonSocial"
              defaultValue={institucion.razonSocial}
              disabled={true}
            />
            <span className="help-block">{errores.razonSocial ? errores.razonSocial[0] : ''}</span>
          </div>
        </div>
      </div>
      <div className='row'>
        <div className='col-md-6'>
          <div className={`form-group ${errores.rfc ? 'has-error has-feedback' : ''}`}>
            <label htmlFor="institucion_rfc">RFC</label>
            <input
              className='form-control'
              type="text"
              name="institucion[rfc]"
              id="institucion_rfc"
              defaultValue={institucion.rfc}
              disabled={true}
            />
            <span className="help-block">{errores.rfc ? errores.rfc[0] : ''}</span>
          </div>
        </div>
        <div className='col-md-6'>
          <div className={`form-group ${errores.representante ? 'has-error has-feedback' : ''}`}>
            <label htmlFor="institucion_representante">Representante</label>
            <input
              className='form-control'
              type="text"
              name="institucion[representante]"
              id="institucion_representante"
              defaultValue={institucion.representante}
              disabled={true}
            />
            <span className="help-block">{errores.representante ? errores.representante[0] : ''}</span>
          </div>
        </div>
      </div>
      <div className='row'>
        <div className="col-md-12">
          <div className={`form-group ${errores.direccion ? 'has-error has-feedback' : ''}`}>
            <label htmlFor="institucion_direccion">Domicilio</label>
            <input
              className='form-control'
              type="text"
              name="institucion[direccion]"
              id="institucion_direccion"
              defaultValue={institucion.direccion}
              disabled={true}
            />
            <span className="help-block">{errores.direccion ? errores.direccion[0] : ''}</span>
          </div>
        </div>
      </div>

      <div className='row'>
        <div className="col-md-4">
          <div className='form-group'>
            <label htmlFor="institucion_correo">Correo</label>
            <input
              className='form-control'
              type="email"
              name="institucion[correo]"
              id="institucion_correo"
              defaultValue={institucion.correo}
              disabled={true}
            />
          </div>
        </div>

        <div className="col-md-3">
          <div className={`form-group ${errores.telefono ? 'has-error has-feedback' : ''}`}>
            <label htmlFor="institucion_telefono">Teléfono</label>
            <input
              className='form-control'
              type="text"
              name="institucion[telefono]"
              id="institucion_telefono"
              defaultValue={institucion.telefono}
              disabled={true}
            />
            <span className="help-block">{errores.telefono ? errores.telefono[0] : ''}</span>
          </div>
        </div>

        <div className="col-md-2">
          <div className={`form-group ${errores.extension ? 'has-error has-feedback' : ''}`}>
            <label htmlFor="institucion_extension">Extensión</label>
            <input
              className='form-control'
              type="text"
              name="institucion[extension]"
              id="institucion_extension"
              defaultValue={institucion.extension}
              disabled={true}
            />
            <span className="help-block">{errores.extension ? errores.extension[0] : ''}</span>
          </div>
        </div>

        <div className="col-md-3">
          <div className='form-group'>
            <label htmlFor="institucion_fax">Fax (opcional)</label>
            <input
              className='form-control'
              type="text"
              name="institucion[fax]"
              id="institucion_fax"
              defaultValue={institucion.fax}
              disabled={true}
            />
          </div>
        </div>
      </div>

      <div className='row'>
        <div className="col-md-4">
          <div className='form-group' >
            <label htmlFor="institucion_sitioWeb">Página web (opcional)</label>
            <input className='form-control'
              type="text"
              name="institucion[sitioWeb]"
              id="institucion_sitioWeb"
              defaultValue={institucion.sitioWeb}
              disabled={true}
            />
          </div>
        </div>
      </div>

      {
        institucion.cedulaIdentificacion &&
        <div className="row">
          <div className="col-md-8">
            <label>Constancia de Situación Fiscal:&nbsp; </label>
            {
              institucion.cedulaIdentificacion &&
              <a
                href={`${getSchemeAndHttpHost()}/fofoe/instituciones/${institucion.id}/descargar-cedula-de-identificacion`}
                target='_blank' download
                download
              >
                Descargar cédula
              </a>
            }
          </div>
        </div>
      }

        <div className="row mt-10">
            <div className="col-md-12">
                <table className='table table-bordered'>
                    <thead className='headers'>
                    <tr>
                        <th>No. Solicitud</th>
                        <th>Fecha Solicitud</th>
                        <th>Referencia</th>
                      <th>Fecha Pago</th>
                        <th>Monto</th>
                        <th>Factura</th>
                        <th>Estado</th>
                    </tr>
                    </thead>
                    <tbody>
                    {
                      pagos.map((item, index) => (
                        <tr key={index}>
                          <td>{item.noSolicitud}</td>
                          <td>{item.fecha ? item.fecha : 'No asignada'}</td>
                          <td>{
                            item.tipoPago == 'Único' ?
                              item.referenciaBancaria ? item.referenciaBancaria : 'No asignada'
                              :
                              <ul>{item.pagos.map((pagos, indexP) => {
                                return (<li key={indexP} >{pagos.referenciaBancaria ? pagos.referenciaBancaria : 'No asignada'}</li>);
                              })}</ul>
                          }
                          </td>
                          <td><ul>{item.pagos.map((pagos, indexP) => {
                            return (<li key={indexP}>{pagos.fechaPagoFormatted ? pagos.fechaPagoFormatted : 'No asignada'}</li>);
                          })}</ul></td>
                          <td><ul>{item.pagos.map((pagos, indexP) => {
                            return (<li key={indexP}>{pagos.monto ? "$ " + numberFormat.format(pagos.monto) : 'No asignada'}</li>);
                          })}</ul></td>

                          <td><ul>{item.pagos.map((pagos, indexP) => {
                            return (<li key={indexP}>{
                                pagos.requiereFactura ?
                                  pagos.factura ?
                                    pagos.factura.zip
                                    : pagos.fechaPagoFormatted ?
                                      'Pendiente de factura'
                                    : 'Pendiente de pago'
                                  : 'No solicitada'}</li>);
                          })}</ul></td>

                          <td>
                            {
                              item.pagos.length < 1 ? item.estatus :
                                <ul>{item.pagos.map((pagos, indexP) => {
                                  return (<li key={indexP}>{
                                    pagos.fechaPagoFormatted ?
                                      pagos.validado ?
                                        ( pagos.validado == true ? 'Pagada' : 'Pendiente de validación')
                                      : 'Pendiente de validación'
                                    : 'Pendiente de pago'
                                  }</li>);
                                })}</ul>
                            }
                            </td>

                        </tr>
                      ))
                    }
                    </tbody>
                </table>
            </div>
        </div>

    </div>
  )
}

export default ValidarInfo

document.addEventListener('DOMContentLoaded', () => {

    const perfilComponent = document.getElementById('perfil-component');
    if (perfilComponent) {
        const root = createRoot(perfilComponent);
        root.render(
            <ValidarInfo
                institucion={window.INSTITUCION_PROP}
                errores={window.ERRORS}
                action={window.ACTION}
                pagos={window.PAGOS_PROP}
            />
        );
    }

    const listaConvenioComponent = document.getElementById('lista-convenio-component');
    if (listaConvenioComponent) {
        const root = createRoot(listaConvenioComponent);
        root.render(
            <ListConvenios
                convenios={window.CONVENIOS_PROP}
            />
        );
    }
});
