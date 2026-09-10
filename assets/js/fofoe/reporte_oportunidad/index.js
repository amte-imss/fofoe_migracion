import * as React from 'react'
import ReactPaginate from "react-paginate";
import ContenedorFiltro from "../../pregrado/components/ContenedorFiltro";
import Buscador from "../../pregrado/components/Buscador";
import OpcionesPageSize from "../../pregrado/components/OpcionesPageSize";
import {getReportePagos, getReportePagosCSV} from "./reportePagos";
import {moneyFormat} from "../../utils";

const ReporteOportunidad = () => {

  const {useState, useEffect} = React
  const [reportePagos, setReportePagos] = useState([])
  const [desdeSel, setDesde] = useState(null)
  const [hastaSel, setHasta] = useState(null)

  const [search, setSearch] = useState('')
  const [isLoading, toggleLoading] = useState(false)
  const [totalItems, setTotalItems] = useState(0)
  const [totalPages, setTotalPages] = useState(0)
  const [currentPage, setCurrentPage] = useState(1)
  const [pageSize, setPageSize] = useState(10)

  useEffect(()=>{
      getDatosReporte();
    }, [])

  function getDatosReporte(pag=1, limit=pageSize) {
    pag = Number.isInteger(pag) ? pag : 1;
    toggleLoading(true);
    getReportePagos( desdeSel, hastaSel, search, pag, limit)
      .then( (res) => {
          setReportePagos(res.reporte)
          setTotalItems( res.totalItems )
          setTotalPages( res.numPags)
          setCurrentPage(pag)
      }
      ).finally(() => {
      toggleLoading(false)
    })
  }

  function exportar() {
    getReportePagosCSV(desdeSel, hastaSel, search);
  }

  let offset = totalItems > 0 ?
    (pageSize*(currentPage-1)) + 1
    : 0;

  let indexRow = 0;

  function handlePageClick(e) {
    getDatosReporte( e.selected + 1 )
  }

  function handleSearch() {
    getDatosReporte();
  }

  return (
    <div className="panel panel-default">

      <div className="panel-heading">
        Pago oportuno de cuotas de recuperación al Fondo de Fomento a la Educación (FOFOE)
      </div>
      <div className="panel-body">

        <div className="row">
          <div className="col-md-2">Período de Consulta de pagos recibidos</div>
          <ContenedorFiltro
            EtiquetaFiltro={"Desde: "}
            setValSel={setDesde}
            valores={[]}
            name="desde"
            tipo="date"
          />
          <ContenedorFiltro
            EtiquetaFiltro={"Hasta: "}
            setValSel={setHasta}
            valores={[]}
            name="desde"
            tipo="date"
          />
        </div>
        <Buscador
          handleSearch={ handleSearch }
          handleExport={ exportar }
          setSearch={setSearch}
        />

        <OpcionesPageSize
          setPageSize={setPageSize}
          handleSearch={handleSearch}
        />
        <div style={{overflowX: 'auto'}}>
        <table className="table" style={{minWidth: '1400px'}}>
          <thead>
          <tr>
            <th rowSpan={2} >Consecutivo</th>
            <th rowSpan={2} >Número de Solicitud</th>
            <th rowSpan={2} >Fecha de Solicitud</th>
            <th rowSpan={2} >Delegación </th>
            <th rowSpan={2}>Campo Clínico</th>
            <th rowSpan={2}>Carrera</th>
            <th rowSpan={2} >Período</th>
            <th rowSpan={2} >Institución </th>
            <th rowSpan={2}>Alumnos</th>
            <th colSpan={4}>Datos de la aportación</th>
            <th rowSpan={2} >Indicador</th>
            <th rowSpan={2} >Dias</th>
          </tr>
          <tr>
            <th>Importe</th>
            <th>Referencia de Pago</th>
            <th>Fecha de depósito</th>
            <th>Fecha de Facturación</th>
          </tr>
          </thead>
          <tbody>
          {
            isLoading ?
              <tr>
                <th className='text-center' colSpan={16}>Cargando información...</th>
              </tr>
              :  reportePagos.length > 0 ?
            reportePagos.map( ( campo ) => (
                <tr key={++indexRow}>
                  <td> {indexRow} </td>
                  <td> {campo.solicitud.noSolicitud || campo.solicitud.id} </td>
                  <td> {campo.solicitud.fecha} </td>
                  <td> {campo.displayDelegacion} </td>
                  <td> {campo.displayCicloAcademico} </td>
                  <td> {campo.displayCarrera} </td>
                  <td>  {campo.fechaInicialFormatted}  / {campo.fechaFinalFormatted} </td>
                  <td> {campo.solicitud.institucion.nombre} </td>
                  <td> {campo.lugaresAutorizados} </td>
                  <td> { moneyFormat(campo.monto) } </td>
                  <td> { campo.referenciaBancaria } </td>
                  <td> { campo.lastPago ? campo.lastPago.fechaPagoFormatted : '' } </td>
                  <td>  { campo.lastPago ?
                      (campo.lastPago.requiereFactura ?
                          (campo.lastPago.factura ? campo.lastPago.factura.fechaFacturacionFormatted : 'PENDIENTE')
                          : 'FACTURA NO SOLICITADA')
                      : ''}  </td>

                  <td> { campo.lastPago && campo.tiempoPago > -1000 ?
                            (campo.tiempoPago >= 14 ? 'CUMPLE' : 'NO CUMPLE')
                          : 'PENDIENTE DE PAGO'} </td>
                  <td> { campo.lastPago && campo.tiempoPago > -1000 ? campo.tiempoPago : '-' }</td>
                </tr>
              ) )
            :
              <tr>
                <td colSpan={16} className="text-center"> No hay registros disponibles </td>
              </tr>
          }
          </tbody>
        </table>
        </div>
        { !isLoading && reportePagos.length > 0 ?
          <div className="col-md-12">
            <div className="col-md-3">
              {offset} - {offset + (reportePagos.length - 1)} de {totalItems}
            </div>
            <div className="col-md-9 text-center">
              <ReactPaginate
                pageCount={totalPages}
                marginPagesDisplayed={5}
                pageRangeDisplayed={3}
                previousLabel={'Anterior'}
                nextLabel={'Siguiente'}
                breakLabel={'...'}
                breakClassName={'break-me'}
                onPageChange={(e) => { handlePageClick(e) }}
                containerClassName={'pagination'}
                subContainerClassName={'pages pagination'}
                activeClassName={'active'}
                forcePage={currentPage - 1}
              />
            </div>
          </div>
          : ''
        }
      </div>
    </div>
  );
};

export default ReporteOportunidad
