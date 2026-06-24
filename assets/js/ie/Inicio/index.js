import ReactDOM from "react-dom";
import React from "react";
import MisSolicitudes from "./MisSolicitudes";
import {createRoot} from "react-dom/client";

document.addEventListener('DOMContentLoaded', () => {
    createRoot(document.getElementById('inicio-component')).render(
        <MisSolicitudes/>
    )
})
