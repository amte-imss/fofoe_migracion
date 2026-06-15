import * as React from 'react'
import ReactDOM from 'react-dom'
import './styles.scss'
import DetalleCamposClinicos from "./DetalleCamposClinicos";
import ReferenciaPago from "./ReferenciaPago";

document.addEventListener('DOMContentLoaded', () => {
  ReactDOM.render(
    <DetalleCamposClinicos
      camposClinicos={window.CAMPOS_CLINICOS_PROPS}
      solicitud={window.SOLICITUD_PROPS}
    />,
    document.getElementById('detalle_campos_clinicos-component')
  )

  if(window.SOLICITUD_PROPS.formatosFofoeCargados) {
    ReactDOM.render(
        <ReferenciaPago
            action={window.ACTION}
            institucion={window.INSTITUCION_PROPS}
            camposClinicos={window.CAMPOS_CLINICOS_PROPS}
        />,
        document.getElementById('referencia_pago-component')
    )
  }
})
