import { createRoot } from "react-dom/client";
import * as React from 'react';
import ValidacionDePagoCC from "./components/ValidacionPagoCC";
import ValidacionPagoPosgrado from "./components/ValidacionPagoPosgrado";

const ValidacionDePago = ({ pago, categoriaPago, pagos, campos }) => {
  return (
    'CC' === categoriaPago ?
      <ValidacionDePagoCC pago={pago} campos={campos} />
      : (
        'POSGRADO' === categoriaPago ?
          <ValidacionPagoPosgrado
            pago={pago}
            pagos={pagos}
            montoAPagar={window.MONTO_A_PAGAR}
          />
          : null
      )
  )
}

export default ValidacionDePago;

document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.getElementById('validacion-de-pago-component');
    if (rootElement) {
        const root = createRoot(rootElement);
        root.render(
            <ValidacionDePago
                pago={window.PAGO_PROPS}
                pagos={window.PAGOS}
                campos={window.CAMPOS}
                categoriaPago={window.CATEGORIA_PAGO}
            />
        );
    }
});
