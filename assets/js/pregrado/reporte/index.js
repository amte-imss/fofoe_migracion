import * as React from 'react'
import { createRoot } from "react-dom/client";
import ReporteDetalle from "../components/ReporteDetalle";

document.addEventListener('DOMContentLoaded', () => {
  createRoot(document.getElementById('reporte-wrapper')).render(
    <ReporteDetalle />,
  )})