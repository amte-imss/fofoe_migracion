import {dateFormat, getSchemeAndHttpHost, moneyFormat} from "../../../utils";
import Cleave from "cleave.js/react";
import * as React from "react";
import Swal from "sweetalert2";

const ValidarComprobantePosgrado = ({pago, montoAPagar}) => {

  const { useState, useRef } = React
  const [monto, setMonto] = useState(pago.monto)
  const [montoPorPagar, setMontoPorPagar] = useState(montoAPagar)
  const [tasaCambio, setTasaCambio] = useState(pago.residencia.tasaCambio ?? '')
  const [isPagoValidado, setPagoValidado] = useState(true)
  const SI_ES_PAGO_CORRECTO_DEFAULT = 1
  const NO_ES_PAGO_CORRECTO_DEFAULT = 0

  const formRef = useRef(null)
  const [errores, setErrores] = React.useState({});

  const handlePagoValidado = ({ target }) => {
    setPagoValidado(parseInt(target.value) === SI_ES_PAGO_CORRECTO_DEFAULT)
  }

  const handleMonto = ({ target }) => {
    setMonto(target.rawValue)
  }

  const handleTasaCambio = ({ target }) => {
    setTasaCambio(target.rawValue)
    let porPagar = parseFloat(pago.residencia.monto)*parseFloat(target.rawValue)
    let totalValidado = 0
    porPagar = porPagar - totalValidado
    setMontoPorPagar(porPagar)
  }

  const isFormValid = () => {
    let result = true;
    let errores = {};
    if (tasaCambio <= 0) {
      errores = Object.assign(errores, {
        tasaCambio: ['El tipo de cambio debe ser mayor que cero']
      });
      result = false;
    }

    if (isPagoValidado && ( parseFloat(montoAPagar)  > parseFloat(monto) )) {
      errores = Object.assign(errores, {
        pagoValido: ['Para que el pago sea válido se debe cubrir por completo el monto a Pagar']
      });
      result = false;
    }

    setErrores(errores);

    return result;
  }

  function handleValidacionDePago(event) {
    event.preventDefault();

    if (!isFormValid()) return;

    Swal.fire({
      title: '¿Estás seguro de continuar?',
      text: 'El monto debe de coincidir con el del comprobante cargado por el residente',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: '¡Si, estoy seguro!',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.value) {
        formRef.current.submit();
      }
    })
  }

  return(
    <div className="col-md-12">
      <div className="row">
        <h2 className='mb-20'>Validar comprobante de pago</h2>
        <div className="col-md-6">
          <p className='mb-5'>Referencia bancaria: <strong>{pago.referenciaBancaria}</strong></p>
          <p className='mb-5'>Monto pendiente a validar:
            { !tasaCambio ?
              <strong>{moneyFormat(pago.residencia.monto)} {pago.residencia.tipoMoneda} </strong>
              :
              <strong> {moneyFormat(montoPorPagar)} MXN </strong>
            }
          </p>
          <p className='mb-20'>Factura: <strong>{pago.requiereFactura ? 'Solicitada' : 'No solicitada'}</strong></p>
        </div>
        <div className="col-md-6">
          <p className='mb-5'>Fecha registrada por residente: <strong>{ dateFormat(pago.fechaPagoRegistrada ?? pago.fechaPago) }</strong></p>
          <p className='mb-5'>Monto registrado por residente: <strong>{ moneyFormat(pago.montoRegistrado ?? pago.monto) } {pago.tipoMoneda}</strong></p>
          <p className='mb-5'>Comprobante de pago a validar:&nbsp;&nbsp;
            <a
              className={"btn btn-primary btn-sm"}
              href={`${getSchemeAndHttpHost()}/fofoe/pagos/${pago.id}/descargar-comprobante-de-pago`}
              download
            >
              Descargar
            </a>
          </p>
        </div>
      </div>
      <form
        action={`${getSchemeAndHttpHost()}/fofoe/pagos/posgrado/${pago.id}/validacion-de-pago`}
        method='post'
        className='form-horizontal'
        encType='multipart/form-data'
        ref={formRef}
        onSubmit={handleValidacionDePago}
      >
        <div className="form-group">
          <label
            htmlFor="validacion_pago_usd_fechaPago"
            className='control-label col-md-4'
          >
            Fecha en que se realizó el nuevo pago:
          </label>
          <div className="col-md-3">
            <input
              type="date"
              id='validacion_pago_usd_fechaPago'
              className='form-control'
              name='validacion_pago_usd[fechaPago]'
              required={true}
              defaultValue={pago.fechaPago.substring(0, 10)}
            />
          </div>
        </div>
        <div className={`form-group ${pago.residencia.tasaCambio ? 'hidden' : ''}`}>
          <label
            htmlFor="validacion_pago_usd_tasaCambio"
            className='control-label col-md-4'
          >
            Tipo de Cambio:
          </label>
            <div className="col-md-3">
              <div className={`input-group`}>
                <div className="input-group-addon">$</div>
                <Cleave
                  options={{numeral: true, numeralThousandsGroupStyle: 'thousand'}}
                  className='form-control'
                  required={!tasaCambio}
                  value={tasaCambio}
                  onChange={handleTasaCambio}
                />
                <input
                  type="hidden"
                  id='validacion_pago_usd_tasaCambio'
                  name='validacion_pago_usd[tasaCambio]'
                  value={tasaCambio}
                />
              </div>
              <span className="bg-danger help-block">{errores.tasaCambio ? errores.tasaCambio[0] : ''}</span>
          </div>
        </div>
        <div className="form-group">
          <label
            htmlFor="validacion_pago_usd_monto"
            className='control-label col-md-4'
          >
            Monto del comprobante a registrar (MXN):<br/>
            <span className='text-danger text-sm'>NOTA: El monto debe coincidir con el comprobante registrado</span>
          </label>
          <div className="col-md-3">
            <div className={`input-group`}>
              <div className="input-group-addon">$</div>
              <Cleave
                options={{numeral: true, numeralThousandsGroupStyle: 'thousand'}}
                className='form-control'
                required={true}
                onChange={handleMonto}
              />
              <input
                type="hidden"
                id='validacion_pago_usd_monto'
                name='validacion_pago_usd[monto]'
                value={monto}
              />
            </div>
          </div>
        </div>
        <div className="form-group">
          <label
            htmlFor='validacion_pago_usd_requiere_factura'
            className="control-label col-md-4 text-right"
          >
            ¿El pago es correcto?&nbsp;
          </label>
          <div className="col-md-3">
            <label htmlFor='validacion_pago_usd_validado_yes'>Si&nbsp;</label>
            <input
              type="radio"
              value={SI_ES_PAGO_CORRECTO_DEFAULT}
              id='validacion_pago_validado_yes'
              name='validacion_pago_usd[validado]'
              required={true}
              onChange={handlePagoValidado}
            />
            &nbsp;&nbsp;&nbsp;&nbsp;
            <label htmlFor="validacion_pago_usd_validado_no">No&nbsp;</label>
            <input
              type="radio"
              value={NO_ES_PAGO_CORRECTO_DEFAULT}
              id='validacion_pago_usd_validado_no'
              name='validacion_pago_usd[validado]'
              required={true}
              onChange={handlePagoValidado}
            />
            <span className="bg-danger help-block">{errores.pagoValido ? errores.pagoValido[0] : ''}</span>
          </div>
        </div>
        {
          !isPagoValidado &&
          <div className="form-group">
            <label
              htmlFor="validacion_pago_usd_observaciones"
              className='control-label col-md-4'
            >
              Observaciones
            </label>
            <div className="col-md-5">
              <textarea
                rows={7}
                className='form-control'
                id='validacion_pago_usd_observaciones'
                name='validacion_pago_usd[observaciones]'
                required={true}
              />
            </div>
          </div>
        }
        <div className="row mt-30">
          <div className="col-md-4"/>
          <div className="col-md-2">
            <a
              href={`${getSchemeAndHttpHost()}/fofoe/pagos/posgrado`}
              className='btn btn-default btn-block'
            >
              Cancelar
            </a>
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
  )
}

export default ValidarComprobantePosgrado