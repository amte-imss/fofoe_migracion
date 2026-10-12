import * as React from 'react'
import { createRoot } from "react-dom/client";
import './styles.scss'
import DetalleSolicitudMultiple from "./Detalle";
import Expediente from "./Expediente";

document.addEventListener('DOMContentLoaded',  () => {
  createRoot(document.getElementById('detalle-solicitud-multiple-component')).render(
    <DetalleSolicitudMultiple
      solicitud={window.SOLICITUD_PROP}
    />,
  )
  createRoot(document.getElementById('expediente-solicitud-multiple-component')).render(
    <Expediente
      solicitud={window.SOLICITUD_PROP}
    />,
  )
})
