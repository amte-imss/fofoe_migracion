import * as React from "react";
import {getSchemeAndHttpHost} from "../../../utils";
import {isPagoCampoClinico, isPagoPosgrado} from "../utils";

const LinkValidarPago = ({pago, asButton}) => {
  const href = isPagoCampoClinico(pago) ?
    `${getSchemeAndHttpHost()}/fofoe/pagos/${pago.id}/validacion-de-pago`
  : isPagoPosgrado(pago) ?
    `${getSchemeAndHttpHost()}/fofoe/pagos/posgrado/${pago.id}/validacion-de-pago`
  : '#'

  return (
    <a
      className={`btn ${ asButton ?  'btn-primary'  : '' } `}
      href={href}>Validar Pago</a>
  )
}

const LinkDetalleReferencia = ({pago, asButton}) => {
  const href = isPagoCampoClinico(pago) ?
    `${getSchemeAndHttpHost()}/fofoe/referencia/${pago.id}`
    : isPagoPosgrado(pago) ?
      `${getSchemeAndHttpHost()}/fofoe/referencia/posgrado/${pago.id}`
      : '#'

  return (
    !asButton ?
    <a
      className={`btn ${ asButton ?  'btn-primary'  : 'btn-link' } `}
      href={href}>Ver Detalle</a>
  : null
  )
}

const RegistroFactura  = ({pago, asButton}) => {
  if(isPagoPosgrado(pago)){
    return (<a className={`btn ${ asButton ?  'btn-primary'  : '' } `} href={`${getSchemeAndHttpHost()}/fofoe/pagos/${pago.id}/registrar-factura-posgrado`}>Registrar Factura</a>);
  }
  return (<a className={`btn ${ asButton ?  'btn-primary'  : '' } `} href={`${getSchemeAndHttpHost()}/fofoe/pagos/${pago.id}/registrar-factura`}>Registrar Factura</a>);
}

const AccionFofoe = ({pago, asButton=false}) => {


  if(pago.validado != null && pago.validado && pago.requiere_factura && !pago.factura_generada){
    return (<RegistroFactura pago={pago} asButton={asButton}/>);
  } else if(pago.validado == null){
    return (<LinkValidarPago pago={pago} asButton={asButton} />);
  } else {
    return (<LinkDetalleReferencia pago={pago} asButton={asButton}/>);
  }
}

export default AccionFofoe