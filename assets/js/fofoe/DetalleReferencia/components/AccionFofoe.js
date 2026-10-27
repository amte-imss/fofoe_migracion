import {getSchemeAndHttpHost} from "../../../utils";
import * as React from "react";

const AccionFofoe = ({pago}) => {
  if (!pago || !pago.comprobantePago) return null;
  const RegistroFactura  = () => (<a className="btn btn-default" href={`${getSchemeAndHttpHost()}/fofoe/pagos/${pago.id}/registrar-factura`}>Registrar Factura</a>);
  const ValidarPago = () => (<a className="btn btn-default" href={`${getSchemeAndHttpHost()}/fofoe/pagos/${pago.id}/validacion-de-pago`}>Validar Pago</a>);

  return (
    <div className="col-md-6">
      {
        pago.validado || pago.validado == null ?
          <strong>Acción </strong> : null
      }
      {
        (pago.validado && pago.requiereFactura && !pago.facturaGenerada) ?
          <RegistroFactura />
          : pago.validado == null ?
            <ValidarPago />
            : null
      }
    </div>
  )
}

export default AccionFofoe