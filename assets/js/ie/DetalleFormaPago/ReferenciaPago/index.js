import * as React from 'react'
import { TIPO_PAGO } from "../../../constants";
import {getSchemeAndHttpHost} from "../../../utils";

const ReferenciaPago = (
  {
    tipoPagoSelected,
    descargarReferenciasBancariasPath,
    solicitud_id
  }
  ) => {

  const { useState } = React
  const [ isDownload, setIsDownload ] = useState(false)

  function handleClickDownload(e) {
      e.preventDefault();
    setIsDownload(true);
    window.open(descargarReferenciasBancariasPath)

    let redirectRoute = `${getSchemeAndHttpHost()}/ie/solicitudes/${solicitud_id}/detalle-de-solicitud`;
    redirectRoute = tipoPagoSelected === TIPO_PAGO.MULTIPLE ? redirectRoute +  `-multiple` : redirectRoute;
    console.log(redirectRoute);
    window.location.href = redirectRoute;

  }

  return(
    <div className='row'>
      <div className="col-md-5">
        <div className="radio">
          <label htmlFor="forma_pago_tipoPago_unico">
            <input
              id='forma_pago_tipoPago_unico'
              type="radio"
              name='forma_pago[tipoPago]'
              value={TIPO_PAGO.UNICO}
              defaultChecked={tipoPagoSelected === TIPO_PAGO.UNICO}
              disabled={true}
            />Generar un único Formato para pagar todos los Campos Clínicos Autorizados en esta solicitud.
          </label>
        </div>
        <p className='text-danger'>Considere que el pago deberá realizarse en una sola exhibición y por el monto total de la solicitud.</p>
      </div>
      <div className="col-md-2"/>
      <div className="col-md-5">
        <div className="radio">
          <label htmlFor="forma_pago_tipoPago_multiple">
            <input
              id='forma_pago_tipoPago_multiple'
              type="radio"
              name='forma_pago[tipoPago]'
              value={TIPO_PAGO.MULTIPLE}
              defaultChecked={tipoPagoSelected === TIPO_PAGO.MULTIPLE}
              disabled={true}
            />Generar un único Formato para pagar todos los Campos Clínicos Autorizados en esta solicitud.
          </label>
        </div>
        <p className='text-danger'>Considere que el pago se realizará de manera individual por cada uno de los campos clínicos solicitados.</p>
      </div>
      <div className="col-md-12 mt-20">
        <p><strong>NOTA: Para realizar su pago, por favor descargue su formato de referencia, es muy importante que está sea incluida por su banco al realizar su pago.</strong></p>
      </div>
      <div className="col-md-12 mt-20">
        <div className="row">
          <div className="col-md-4"/>
          <div className="col-md-4">
            {
              !isDownload ?
                <a
                  href="#"
                  className='btn btn-success btn-block'
                  download
                  onClick={handleClickDownload}
                >
                  ¡Descargar referencia bancaria!
                </a> :
                <button
                  type='button'
                  className='btn btn-success btn-block'
                  disabled={true}
                >
                  Descargando referencias bancarias, por favor espere...
                </button>
            }
          </div>
        </div>
      </div>
    </div>
  )
}

export default ReferenciaPago
