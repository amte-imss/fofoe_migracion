import * as React from 'react'
import ReactDOM, { render } from "react-dom";
import ReporteDetalle from "../components/ReporteDetalle";

document.addEventListener('DOMContentLoaded', () => {
  ReactDOM.render(
    <ReporteDetalle />,
    document.getElementById('reporte-wrapper')
  )
})