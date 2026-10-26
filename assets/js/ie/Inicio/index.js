import { createRoot } from "react-dom/client";
import React from "react";
import MisSolicitudes from "./MisSolicitudes";

document.addEventListener('DOMContentLoaded', () => {
    createRoot(document.getElementById('inicio-component')).render(
        <MisSolicitudes/>
    )
})
