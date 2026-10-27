import * as React from "react";
import {getSchemeAndHttpHost} from "../../../utils";

const Facturas = ({pago}) => {
  if(pago.requiere_factura && !pago.factura_generada) {
    return (<span>Pendiente</span>);
  }else if(pago.factura_generada){
    return (<a href={`${getSchemeAndHttpHost()}/fofoe/factura/${pago.factura_id}/download`}>{pago.factura_folio}</a>);
  }else{
    return (<span>No Requerida</span>);
  }
}

export default Facturas