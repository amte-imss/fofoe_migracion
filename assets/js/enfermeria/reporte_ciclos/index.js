import * as React from 'react'
import { createRoot } from "react-dom/client";
import ReporteUnidad from "../components/ReporteUnidad";

document.addEventListener('DOMContentLoaded', () => {
  createRoot(document.getElementById('reporte-wrapper')).render(
    <ReporteUnidad />,
  )})