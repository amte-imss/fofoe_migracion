import * as React from 'react'
import {dateFormat, getSchemeAndHttpHost, moneyFormat} from "../../../utils";

const HistorialFacturas = ({facturas}) => {
  return (
    <div className="row">
      <div className="col-md-12">
        <p className="mt-10 mb-10"><strong>Facturas generadas</strong></p>
        <table className='table'>
          <thead className='headers'>
          <tr>
            <th>Fecha Facturación</th>
            <th>Monto Facturado</th>
            <th>Archivo Factura</th>
            <th>Folio Factura</th>
          </tr>
          </thead>
          <tbody>
          {
            facturas.length > 0 ?

              facturas.map((factura, index) =>
                <tr key={index}>
                  <td>{dateFormat(factura.fechaFacturacion)}</td>
                  <td>{moneyFormat(factura.monto)}</td>
                  <td>{factura.zip && <a href={`${getSchemeAndHttpHost()}/fofoe/factura/${factura.id}/download`}>{factura.zip}</a>}</td>
                  <td>{factura.folio}</td>
                </tr>
              )
              :
              <tr>
                <td className='text-center text-info' colSpan={4} ><strong>No hay registros disponibles</strong></td>
              </tr>
          }
          </tbody>
        </table>
      </div>
    </div>
  )
}

export default HistorialFacturas