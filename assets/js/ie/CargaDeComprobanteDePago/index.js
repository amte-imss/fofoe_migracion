import * as React from 'react';
import ReactDOM from 'react-dom';
import {checkInputFile, getSchemeAndHttpHost, moneyFormat} from "../../utils";
import Cleave from "cleave.js/react";
import { TIPO_PAGO } from "../../constants";
const SI_REQUIERE_FACTURA_DEFAULT = 1;
const NO_REQUIERE_FACTURA_DEFAULT = 0;

const CargaDeComprobanteDePago = (
    {
      gestionPago,
      pagoId,
      institucion,
      errors
    }
  ) => {
  const { useState } = React

  const [monto, setMonto] = useState(undefined)
  const [hasMontoError, setMontoError] = useState(false)
  const [requiereFactura, setRequiereFactura] = useState(undefined)
  const [newCFDI, setNewCFDI] = useState(false)

  function handleMonto({ target }) {
    setMonto(target.rawValue)
  }

  function isPagoMultiple() {
    return gestionPago.tipoPago === TIPO_PAGO.MULTIPLE;
  }

  function getNombreTipoCampo(tipo) {
    var nombre = "Campo clínico";
    switch (tipo) {
      case 1:
        nombre="Ciclo clínico del área de la salud";
        break;
      case 2:
        nombre="Internado Médico";
        break;
   }
    return nombre;
  }

  function handleCargarComprobanteDePago(event) {
    event.preventDefault();

    if(parseFloat(monto) >= parseFloat(gestionPago.montoTotalPorPagar)) {
      setMontoError(false);
      event.target.submit()
      return;
    }

    setMontoError(true)
  }

  return(
    <div className='row'>
      <div className="col-md-12">
        <h2 className='mb-20'>Carga de comprobante de pago</h2>
        <div className="row">
          <div className="col-md-6">
            <p className='mb-5'>No. de Solicitud <strong>{gestionPago.noSolicitud}</strong></p>
            <p className='mb-20'>No. de referencia bancaria <strong>{gestionPago.referenciaBancaria}</strong></p>
            <p className='mb-5'>Tipo de pago <strong>{gestionPago.tipoPago}</strong></p>
            <p className='mb-5'>Monto total: <strong>{moneyFormat(gestionPago.montoTotal)}</strong></p>
          </div>
          {
            isPagoMultiple() &&
            <div className="col-md-6">
              <p className='mb-5'><strong>
                {getNombreTipoCampo(gestionPago.campoClinico.tipoCampoClinico)}</strong></p>
              <p className='mb-5'>Sede: <strong>{gestionPago.campoClinico.sede}</strong></p>
              <p className='mb-5'>Carrera: <strong>{gestionPago.campoClinico.carrera}</strong></p>
              {
                gestionPago.campoClinico.asignatura ?
                  <p className='mb-5'>Asignatura: <strong>{gestionPago.campoClinico.asignatura}</strong></p>
                  : null
              }
            </div>
          }
        </div>
      </div>
      {
        gestionPago.pagos.length !== 0 ?
            <div className="col-md-12 mb-20">
              <table className='table table-condensed'>
                <thead>
                <tr>
                  <th>Comprobante registrado</th>
                  <th>Fecha</th>
                  <th>Monto validado</th>
                  <th>Observaciones</th>
                </tr>
                </thead>
                <tbody>
                {
                  gestionPago.pagos.length !== 0 ?
                      gestionPago.pagos.map((pago, index) =>
                          <tr key={index}>
                            <td><a href={`${getSchemeAndHttpHost()}/ie/pagos/${pago.id}/descargar-comprobante-de-pago`}
                                   target='_blank' download >Descargar</a></td>
                            <td>{pago.fechaPago}</td>
                            <td>{moneyFormat(pago.monto)}</td>
                            <td>{pago.observaciones}</td>
                          </tr>
                      ) :
                      <tr>
                        <td
                            className='text-center text-info'
                            colSpan={4}
                        >
                          Aún no se ha cargado ningún comprobante de pago
                        </td>
                      </tr>
                }
                </tbody>
              </table>
            </div>
        : null
      }
      {
        gestionPago.ultimoPago.observaciones &&
        <div className="col-md-12">
          <h3 className='mb-5'>Observaciones</h3>
          <div className="alert alert-info">
            <p>{gestionPago.ultimoPago.observaciones}</p>
          </div>
        </div>
      }
      <div className="col-md-12">
        <h3 className='mb-5'>Registrar comprobante de pago</h3>
        <p className='mb-20'>Monto total a pagar: <strong>{moneyFormat(gestionPago.montoTotalPorPagar)}</strong></p>
        <form
          action={`${getSchemeAndHttpHost()}/ie/pagos/${pagoId}/carga-de-comprobante-de-pago`}
          method='post'
          className='form-horizontal'
          encType='multipart/form-data'
          onSubmit={handleCargarComprobanteDePago}
        >
          <div className="form-group">
            <label
              htmlFor="comprobante_pago_fechaPago"
              className='control-label col-md-4'
            >
              Fecha en que se realizó el nuevo pago:
            </label>
            <div className="col-md-3">
              <input
                type="date"
                id='comprobante_pago_fechaPago'
                className='form-control'
                name='comprobante_pago[fechaPago]'
                max={new Date().toISOString().split('T')[0]}
                required={true}
              />
            </div>
          </div>
          <div className="form-group">
            <label
              htmlFor="comprobante_pago_monto"
              className='control-label col-md-4'
            >
              Monto del comprobante a registrar:<br/>
              <span className='text-danger text-sm'>NOTA: El monto debe coincidir con el comprobante registrado</span>
            </label>
            <div className="col-md-3">
              <div className={`input-group ${hasMontoError && 'has-error'}`}>
                <div className="input-group-addon">$</div>
                <Cleave
                  options={{numeral: true, numeralThousandsGroupStyle: 'thousand'}}
                  className='form-control'
                  required={true}
                  onChange={handleMonto}
                />
                <input
                  type="hidden"
                  id='comprobante_pago_monto'
                  name='comprobante_pago[monto]'
                  defaultValue={monto}
                />
              </div>
              { hasMontoError && <span className='text-danger'>El monto registrado es menor al monto total a pagar.</span> }
            </div>
          </div>
          <div className="form-group">
            <label
              htmlFor="comprobante_pago_comprobantePagoFile"
              className='control-label col-md-4'
            >
              Cargar comprobante del nuevo pago
            </label>
            <div className="col-md-3">
              <input
                type="file"
                onInput={event => checkInputFile(event.target, {size: 2097152 })}
                id='comprobante_pago_comprobantePagoFile'
                name='comprobante_pago[comprobantePagoFile]'
                className='form-control'
                required={true}
              />
              <span className="text-danger ">{errors.comprobantePagoFile ? errors.comprobantePagoFile[0] : ''}</span>
              <span className="text-danger ">{errors.comprobante_pago_file ? errors.comprobante_pago_file[0] : ''}</span>
            </div>
          </div>
          <div className="form-group">
            <label
              htmlFor='comprobante_pago_requiere_factura'
              className="control-label col-md-4 text-right"
            >
              ¿Requiere factura?&nbsp;
            </label>
            <span className="text-danger">{errors.cedulaFile ? errors.cedulaFile[0] : ''}</span>
            <div className="col-md-3">
              <label htmlFor='comprobante_pago_requiereFactura_yes'>Si&nbsp;</label>
              <input
                type="radio"
                value={SI_REQUIERE_FACTURA_DEFAULT}
                id='comprobante_pago_requiereFactura_yes'
                name='comprobante_pago[requiereFactura]'
                onClick={()=>setRequiereFactura(SI_REQUIERE_FACTURA_DEFAULT)}
                required={true}
              />
              &nbsp;&nbsp;&nbsp;&nbsp;
              <label htmlFor="comprobante_pago_requiereFactura_no">No&nbsp;</label>
              <input
                type="radio"
                value={NO_REQUIERE_FACTURA_DEFAULT}
                id='comprobante_pago_requiereFactura_no'
                name='comprobante_pago[requiereFactura]'
                onClick={()=>setRequiereFactura(NO_REQUIERE_FACTURA_DEFAULT)}
                required={true}
              />
            </div>
          </div>
          {
            requiereFactura ?
            <div className="form-group">
              <div className="col-md-8">
                <label htmlFor="institucion_cedulaFile">
                  Adjunte Constancia de Situación Fiscal Actualizada (No mayor a 3 meses) <br/>
                  <span className='text-danger text-sm'>Por favor verifique que los datos que aparecen en su archivo sean correctos. <br/>
            La constancia de situación fiscal se utilizará para emitir las facturas de sus pagos.</span>
                </label>
              </div>

              <div className="col-md-3">
                <input
                    type="file"
                    id='institucion_cedulaFile'
                    name='comprobante_pago[cedulaFile]'
                    className='form-control'
                    onChange={(event) => checkInputFile(event.target,{size: 2097152 },() => setNewCFDI(true))  }
                    required={true}
                />
                {
                  false &&
                  institucion.cedulaIdentificacion && !newCFDI &&
                  <a
                      href={`${getSchemeAndHttpHost()}/ie/descargar-cedula-de-identificacion-fiscal`}
                      target="_blank"
                      download
                  >
                    Descargar cédula
                  </a>
                }
              </div>
            </div> : null
          }
          <div className="row mt-30">
            <div className="col-md-4"/>
            <div className="col-md-2">
              <a
                href={`${getSchemeAndHttpHost()}/ie/inicio`}
                className='btn btn-default btn-block'
              >Cancelar</a>
            </div>
            <div className="col-md-2">
              <button
                type='submit'
                className='btn btn-success btn-block'>
                Guardar
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  )
}

document.addEventListener('DOMContentLoaded', () => {
  ReactDOM.render(
    <CargaDeComprobanteDePago
      gestionPago={window.GESTION_PAGO_PROPS}
      pagoId={window.PAGO_ID_PROPS}
      institucion={window.INSTITUCION_PROPS}
      errors={window.ERRORS_PROPS}
    />,
    document.getElementById('cargar-de-comprobante-de-pago-component')
  )
})
