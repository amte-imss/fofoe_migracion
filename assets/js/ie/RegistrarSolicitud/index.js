import { createRoot } from "react-dom/client";
import React from "react";
import FormularioSolicitud from "./FormularioSolicitud";

document.addEventListener('DOMContentLoaded', () => {
    createRoot(document.getElementById('registro-component')).render(
        <FormularioSolicitud
            solicitudPrev={window.SOLICITUD_PROP}
        />
    )
})
