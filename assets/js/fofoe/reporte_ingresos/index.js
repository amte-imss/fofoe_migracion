import * as React from 'react'
import {getReporteIngresos} from "./reporteIngresos";
import {getSchemeAndHttpHost, moneyFormat} from "../../utils";

const numberFormat = new Intl.NumberFormat('es-MX', {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2
})
const ReporteIngresos = () => {

  const {useState, useEffect} = React
  const [reporteIngresos, setReporteIngresos] = useState([]);
  const [isLoading, toggleLoading] = useState(false);
  const [anioSel, setAnioSel] = useState(new Date().getFullYear());

  useEffect(() => {
    getDatosReporte(anioSel);
  }, []);

  function getDatosReporte(anio) {
    toggleLoading(true);
    getReporteIngresos(anio).then((res) => {
      setReporteIngresos(res.reporte)
    }).finally( () => {
        toggleLoading(false);
    })
  }

  function exportar() {
    getReporteIngresos(anioSel, 1);
  }

  function handlerAnioSel(e) {
    setAnioSel(e.value !== '' ? e.value : new Date().getFullYear());
    getDatosReporte(e.value);
  }

  let urlExport = `${getSchemeAndHttpHost()}/fofoe/reporte_ingresos?anio=${anioSel}&export=1`
  let totalEnfVal = 0, totalEnfPend = 0
  let totalExtVal = 0, totalExtPend = 0
  let totalCCVal = 0, totalCCPend = 0
  let totalIntVal = 0, totalIntPend = 0
  let totalPresVal = 0, totalPresPend = 0
  let totalSimVal = 0, totalSimPend = 0
  let totalDistVal = 0, totalDistPend = 0
  let anios = ['2020', '2021', '2022', '2023', '2024', '2025', '2026'];

  return (
    <div className="panel panel-default">

      <div className="panel-heading">
        Reporte de ingresos por concepto de Campos Clínicos
      </div>
      <div style={{padding: '15px 15px 0 15px', display: 'flex', gap: '20px', alignItems: 'flex-end'}}>
        <div>
          <label htmlFor="anio">Año de consulta:</label>
          <select
            name="anio"
            className='form-control'
            value={anioSel}
            onChange={({target}) => handlerAnioSel(target)}
            style={{width: '150px'}}
          >
            { anios.map((valor) =>
              <option  value={valor} key={valor} >
                {valor}
              </option>
            )}
          </select>
        </div>
        <div>
          <a href={urlExport} className="btn btn-primary">Descargar CSV</a>
        </div>
      </div>
      <div className="panel-body" style={{overflowX: 'auto', padding: '0', paddingTop: '15px'}}>
        <table className="table table-bordered" style={{marginLeft: 0, width: 'auto'}}>
          <thead>
          <tr>
            <th rowSpan={2}>Mes/Año</th>
            <th colSpan={2}>Enfermería IMSS</th>
            <th colSpan={2}>Extranjeros</th>
            <th colSpan={2}>Ciclos Clínicos</th>
            <th colSpan={2}>Internado Médico</th>
            <th colSpan={2}>Edu Per Presencial</th>
            <th colSpan={2}>Edu Per Simulación</th>
            <th colSpan={2}>Edu Per A Distancia</th>
            <th rowSpan={2}>Total Mensual</th>
          </tr>
          <tr>
            <th>Validado</th>
            <th>Pendiente</th>
            <th>Validado</th>
            <th>Pendiente</th>
            <th>Validado</th>
            <th>Pendiente</th>
            <th>Validado</th>
            <th>Pendiente</th>
            <th>Validado</th>
            <th>Pendiente</th>
            <th>Validado</th>
            <th>Pendiente</th>
            <th>Validado</th>
            <th>Pendiente</th>
          </tr>
          </thead>
          <tbody>
          { isLoading ?
            <tr>
              <td className='text-center' colSpan={16}> Cargando información ... </td>
            </tr>
            : reporteIngresos.length > 0 ?
              reporteIngresos.map( (ingresos, index) => {
                const enfVal = parseFloat(ingresos.enfVal || 0);
                const enfPend = parseFloat(ingresos.enfPend || 0);
                const extVal = parseFloat(ingresos.extVal || 0);
                const extPend = parseFloat(ingresos.extPend || 0);
                const ccVal = parseFloat(ingresos.ccVal || 0);
                const ccPend = parseFloat(ingresos.ccPend || 0);
                const intVal = parseFloat(ingresos.intVal || 0);
                const intPend = parseFloat(ingresos.intPend || 0);
                const presVal = parseFloat(ingresos.presVal || 0);
                const presPend = parseFloat(ingresos.presPend || 0);
                const simVal = parseFloat(ingresos.simVal || 0);
                const simPend = parseFloat(ingresos.simPend || 0);
                const distVal = parseFloat(ingresos.distVal || 0);
                const distPend = parseFloat(ingresos.distPend || 0);
                
                const totalMensual = enfVal + enfPend + extVal + extPend + ccVal + ccPend + 
                                    intVal + intPend + presVal + presPend + simVal + simPend + 
                                    distVal + distPend;
                
                totalEnfVal += enfVal; totalEnfPend += enfPend;
                totalExtVal += extVal; totalExtPend += extPend;
                totalCCVal += ccVal; totalCCPend += ccPend;
                totalIntVal += intVal; totalIntPend += intPend;
                totalPresVal += presVal; totalPresPend += presPend;
                totalSimVal += simVal; totalSimPend += simPend;
                totalDistVal += distVal; totalDistPend += distPend;
                
                return (
                  <tr key={index}>
                    <td>{ingresos.Mes} / {ingresos.Anio}</td>
                    <td>{moneyFormat(enfVal)}</td>
                    <td>{moneyFormat(enfPend)}</td>
                    <td>{moneyFormat(extVal)}</td>
                    <td>{moneyFormat(extPend)}</td>
                    <td>{moneyFormat(ccVal)}</td>
                    <td>{moneyFormat(ccPend)}</td>
                    <td>{moneyFormat(intVal)}</td>
                    <td>{moneyFormat(intPend)}</td>
                    <td>{moneyFormat(presVal)}</td>
                    <td>{moneyFormat(presPend)}</td>
                    <td>{moneyFormat(simVal)}</td>
                    <td>{moneyFormat(simPend)}</td>
                    <td>{moneyFormat(distVal)}</td>
                    <td>{moneyFormat(distPend)}</td>
                    <td><strong>{moneyFormat(totalMensual)}</strong></td>
                  </tr>
                )
              }
                )
            :
            <tr>
              <td className='text-center' colSpan={16}>No hay registros disponibles</td>
            </tr>
          }
          </tbody>
          { isLoading ?
              null
            : reporteIngresos.length > 0 ?
            <tfoot>
            <tr>
            <td><strong>Total</strong></td>
            <td>{moneyFormat(totalEnfVal)}</td>
            <td>{moneyFormat(totalEnfPend)}</td>
            <td>{moneyFormat(totalExtVal)}</td>
            <td>{moneyFormat(totalExtPend)}</td>
            <td>{moneyFormat(totalCCVal)}</td>
            <td>{moneyFormat(totalCCPend)}</td>
            <td>{moneyFormat(totalIntVal)}</td>
            <td>{moneyFormat(totalIntPend)}</td>
            <td>{moneyFormat(totalPresVal)}</td>
            <td>{moneyFormat(totalPresPend)}</td>
            <td>{moneyFormat(totalSimVal)}</td>
            <td>{moneyFormat(totalSimPend)}</td>
            <td>{moneyFormat(totalDistVal)}</td>
            <td>{moneyFormat(totalDistPend)}</td>
            <td><strong>{moneyFormat(totalEnfVal + totalEnfPend + totalExtVal + totalExtPend + 
                                     totalCCVal + totalCCPend + totalIntVal + totalIntPend +
                                     totalPresVal + totalPresPend + totalSimVal + totalSimPend +
                                     totalDistVal + totalDistPend)}</strong></td>
            </tr>
            </tfoot>
            : null
          }
        </table>
      </div>
    </div>
  );
};

export default ReporteIngresos


