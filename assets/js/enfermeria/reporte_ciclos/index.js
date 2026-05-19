import * as React from 'react'
import ReactDOM, { render } from "react-dom";
import ReporteUnidad from "../components/ReporteUnidad";

document.addEventListener('DOMContentLoaded', () => {
  ReactDOM.render(
    <ReporteUnidad />,
    document.getElementById('reporte-wrapper')
  )
})