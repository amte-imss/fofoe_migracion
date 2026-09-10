import * as React from 'react'
import {getSchemeAndHttpHost, moneyFormat} from "../../../utils";

const HistorialPagos = ({pagos}) => {

  return (
    pagos.length > 0 &&
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
          pagos.length !== 0 ?
            pagos.map((pago, index) =>
              <tr key={index}>
                <td>
                  {
                    pago.comprobantePago ?
                      <a href={`${getSchemeAndHttpHost()}/fofoe/pagos/${pago.id}/descargar-comprobante-de-pago`}
                         download>Descargar</a>
                      : 'Pendiente de cargar'
                  }
                </td>

                <td>{pago.fechaPagoFormatted}</td>
                <td>{ pago.validado != null ?  moneyFormat(pago.monto) + 'MXN' : '-'} </td>
                <td>{ pago.fechaPagoFormatted ? pago.observaciones : 'Pendiente de cargar'}</td>
              </tr>
            ) :
            <tr>
              <td
                className='text-center text-info'
                colSpan={4}
              >
                Aún no se ha registrado ningún comprobante de pago
              </td>
            </tr>
        }
        </tbody>
      </table>
    </div>
  )
}

export default HistorialPagos