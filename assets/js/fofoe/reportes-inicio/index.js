import { createRoot } from "react-dom/client";
import * as React from 'react'
import ReporteIngresos from '../reporte_ingresos/index'
import ReporteOportunidad from '../reporte_oportunidad/index'
import ReporteDetalle from "../../pregrado/components/ReporteDetalle";
import ReporteUnidad from "../../enfermeria/components/ReporteUnidad";

const ReportesInicio = () => {

  const {useState, useEffect} = React
  const [reporteActivo, setReporteActivo] = useState('ingresos')
  const [isLoading, toggleLoading] = useState(false)

  function cambiaReporte(tipoReporte) {
    toggleLoading(true);
    setReporteActivo(tipoReporte);
    toggleLoading(false);
  }

  return (
    <div>
      <div>
        <ul className="nav nav-pills nav-fill">
          <li className={(reporteActivo == 'ingresos') ? 'nav-item active' : 'nav-item'} >
            <a className="nav-link" href="#" onClick={()=>cambiaReporte('ingresos')} >Ingresos</a>
          </li>
          <li className={(reporteActivo == 'oportunidad') ? 'nav-item active' : 'nav-item'} >
            <a className="nav-link" href="#" onClick={()=>cambiaReporte('oportunidad')}>Oportunidad de Pago</a>
          </li>
          <li className={(reporteActivo == 'detalle') ? 'nav-item active' : 'nav-item'}>
            <a className="nav-link" href="#" onClick={()=>cambiaReporte('detalle')} >Detalle CCS/INT</a>
          </li>
          <li className={(reporteActivo == 'unidad') ? 'nav-item active' : 'nav-item'}>
            <a className="nav-link" href="#" onClick={()=>cambiaReporte('unidad')}>CCS/INT por Unidad</a>
          </li>
        </ul>
      </div>
      <div className="tab-content" id="myTabContent">
        { (reporteActivo == 'ingresos') ?
            <ReporteIngresos />
            : (reporteActivo == 'oportunidad') ?
              <ReporteOportunidad />
            : (reporteActivo == 'unidad') ?
              <ReporteUnidad />
           :  (reporteActivo == 'detalle') ?
              <ReporteDetalle />
          : <h2> Seleccione una de las opciones </h2>
        }
      </div>
    </div>
  );
}

document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.getElementById('reportes-container');
    if (rootElement) {
        const root = createRoot(rootElement);
        root.render(<ReportesInicio/>);
    }
})
