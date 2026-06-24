import * as React from 'react'
import ReactDOM from 'react-dom'
import './styles.scss'
import DetalleSolicitudMultiple from "./Detalle";
import Expediente from "./Expediente";

document.addEventListener('DOMContentLoaded',  () => {
  ReactDOM.render(
    <DetalleSolicitudMultiple
      solicitud={window.SOLICITUD_PROP}
    />,
    document.getElementById('detalle-solicitud-multiple-component')
  )
  ReactDOM.render(
    <Expediente
      solicitud={window.SOLICITUD_PROP}
    />,
    document.getElementById('expediente-solicitud-multiple-component')
  )
})
