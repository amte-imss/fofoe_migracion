import * as React from 'react'
import { createRoot } from "react-dom/client";
import ReferenciaPago from "./ReferenciaPago";

document.addEventListener('DOMContentLoaded', () => {
  createRoot(document.getElementById('detalle-forma-pago-component')).render(
    <ReferenciaPago
      tipoPagoSelected={window.TIPO_PAGO_SELECTED}
      descargarReferenciasBancariasPath={window.DESCARGAR_REFERENCIAS_BANCARIAS_PATH}
      solicitud_id={window.SOLICITUD_ID}
    />,
  )})
