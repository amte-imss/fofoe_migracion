import ReactDOM from "react-dom";
import React from "react";
import FormularioSolicitud from "./FormularioSolicitud";

document.addEventListener('DOMContentLoaded', () => {
  ReactDOM.render(
    <FormularioSolicitud
      solicitudPrev={window.SOLICITUD_PROP}
    />,
    document.getElementById('registro-component')
  )
})
