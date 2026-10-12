import * as React from 'react'
import { createRoot } from "react-dom/client";
import './styles.scss'
import DetalleCamposClinicos from "./DetalleCamposClinicos";
import ReferenciaPago from "./ReferenciaPago";

document.addEventListener('DOMContentLoaded', () => {
  createRoot(document.getElementById('detalle_campos_clinicos-component')).render(
    <DetalleCamposClinicos
      camposClinicos={window.CAMPOS_CLINICOS_PROPS}
      solicitud={window.SOLICITUD_PROPS}
    />,
  )

  if (window.SOLICITUD_PROPS.formatosFofoeCargados) {
    createRoot(document.getElementById('referencia_pago-component')).render(
        <ReferenciaPago
            action={window.ACTION}
            institucion={window.INSTITUCION_PROPS}
            camposClinicos={window.CAMPOS_CLINICOS_PROPS}
        />,
    )
  }
})
