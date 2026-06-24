import ReactDOM from "react-dom";
import React from "react";
import FormularioSolicitud from "./FormularioSolicitud";
import {createRoot} from "react-dom/client";

document.addEventListener('DOMContentLoaded', () => {
    createRoot(document.getElementById('registro-component')).render(
        <FormularioSolicitud
            solicitudPrev={window.SOLICITUD_PROP}
        />
    )
})
