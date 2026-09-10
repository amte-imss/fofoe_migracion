import {getSchemeAndHttpHost} from "../../../utils";
import Facturas from "./Facturas";
import EstadosPago from "./EstadosPago";
import AccionFofoe from "./AccionFofoe";
import * as React from "react";


const numberFormat = new Intl.NumberFormat('es-MX', {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2
})

const BodyTableReferenciasCC = ({pagos}) => {
  return(
    <tbody>
    <tr style={{textAlign: 'center', display: (pagos.length <= 0 ? 'table-row': 'none'), padding:'80px 0px'}}>
      <td colSpan={9}>
        <h3>No se encontró ningún registro.</h3>
      </td>
    </tr>
    {pagos.map(pago => {
      return (
        <tr key={pago.id}>
          <td>{pago.delegacion}</td>
          <td><a href={`${getSchemeAndHttpHost()}/fofoe/detalle-ie/${pago.institucion_id}`}>{pago.institucion_nombre}</a></td>
          <td>{pago.no_solicitud}</td>
          <td><a href={`${getSchemeAndHttpHost()}/fofoe/referencia/${pago.id}`}>{pago.referencia_bancaria}</a></td>
          <td>$ {numberFormat.format(pago.monto.toString())}</td>
          <td><Facturas pago={pago}/></td>
          <td>{pago.fecha_pago}</td>
          <td><EstadosPago pago={pago}/></td>
          <td><AccionFofoe pago={pago} /></td>
        </tr>
      )
    })}
    </tbody>
  )
}

const BodyTableReferenciasPosgrado = ({pagos}) => {
  return(
    <tbody>
    <tr style={{textAlign: 'center', display: (pagos.length <= 0 ? 'table-row': 'none'), padding:'80px 0px'}}>
      <td colSpan={8}>
        <h3>No se encontró ningún registro.</h3>
      </td>
    </tr>
    {pagos.map(pago => {
      return (
        <tr key={pago.id}>
          <td>{pago.delegacion} {pago.tipo_delegacion_umae}</td>
          <td>{pago.residente_nombre} {pago.residente_apellido_paterno} {pago.residente_apellido_materno}</td>
          <td><a href={`${getSchemeAndHttpHost()}/fofoe/referencia/posgrado/${pago.id}`}>{pago.referencia_bancaria}</a></td>
          <td>$ {numberFormat.format(pago.monto.toString())} {pago.tipo_moneda}</td>
          <td><Facturas pago={pago}/></td>
          <td>{pago.fecha_pago}</td>
          <td><EstadosPago pago={pago}/></td>
          <td><AccionFofoe pago={pago} /></td>
        </tr>
      )
    })}
    </tbody>
  )
}



const BodyTableReferencias = ({pagos, categoriaPago}) => {
  return (
    'CC' === categoriaPago ?
      <BodyTableReferenciasCC pagos={pagos} />
      : (
        'POSGRADO' === categoriaPago ?
          <BodyTableReferenciasPosgrado pagos={pagos} />
          : null
      )
  )
}

export default BodyTableReferencias