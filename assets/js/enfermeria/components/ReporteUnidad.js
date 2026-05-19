import * as React from 'react'
import {getReporteCampos, getReporteCamposCSV} from "../reporte_ciclos/reporteCampos";
import OpcionesPageSize from "../../pregrado/components/OpcionesPageSize";
import ReactPaginate from "react-paginate";
import {getCarreras, getCiclosAcademicos, getDelegaciones, getUnidades} from "../../pregrado/api/catalogos";
import ContenedorFiltro from "../../pregrado/components/ContenedorFiltro";
import Buscador from "../../pregrado/components/Buscador";

const ReporteUnidad = () => {

  const {useState, useEffect} = React
  const [reporteCiclos, setReporteCiclos] = useState([])
  const [desdeSel, setDesde] = useState(null)
  const [hastaSel, setHasta] = useState(null)
  const [delegacionSel, setDelegacionSel] = useState(null)
  const [unidadSel, setUnidadSel] = useState(null)
  const [carreraSel, setCarreraSel] = useState(null)
  const [cicloSel, setCicloSel] = useState(null)
  const [search, setSearch] = useState('')
  const [isLoading, toggleLoading] = useState(false)
  const [totalItems, setTotalItems] = useState(0)
  const [totalPages, setTotalPages] = useState(0)
  const [currentPage, setCurrentPage] = useState(1)
  const [pageSize, setPageSize] = useState(10)
  useEffect(()=>{
    getDatosReporte();
  }, []);

  function getDatosReporte(pag=1, limit=pageSize) {
    pag = Number.isInteger(pag) ? pag : 1;
    toggleLoading(true);
    getReporteCampos(
      desdeSel, hastaSel, delegacionSel, unidadSel,
      carreraSel, cicloSel, search, pag, limit)
      .then( (res) => {
          setReporteCiclos( res.camposClinicos )
          setTotalItems( res.totalItems )
          setTotalPages( res.numPags)
          setCurrentPage(pag)
        }
      ).finally(() => {
      toggleLoading(false)
    })
  }

  function exportar() {
    getReporteCamposCSV(
      desdeSel, hastaSel, delegacionSel, unidadSel,
      carreraSel, cicloSel, search);
  }

  let offset = totalItems > 0 ?
    (pageSize*(currentPage-1)) + 1
    : 0;

  let indexRow = 0;

  function handlePageClick(e) {
    getDatosReporte( e.selected + 1 )
  }

  function handleSearch(numPage, pagSize) {
    getDatosReporte(1, pagSize);
  }

  return (
    <div className="panel panel-default">
      <div className="panel-heading">
        Reporte de Campos Clínicos por Unidad
      </div>
      <div className="panel-body">
        <Filtros
          setDesde={setDesde}
          setHasta={setHasta}
          setDelegacionSel={setDelegacionSel}
          setUnidadSel={setUnidadSel}
          setCarreraSel={setCarreraSel}
          setCicloSel={setCicloSel}
          setSearch={setSearch}
          handleSearch={handleSearch}
          exportar={exportar}
        />
        <OpcionesPageSize
          setPageSize={setPageSize}
          handleSearch={getDatosReporte}
        />
        <table className="table">
          <thead>
          <tr>
            <th>Delegación</th>
            <th>Unidad</th>
            <th>Carrera</th>
            <th>Asignatura</th>
            <th>Institución Educativa</th>
            <th>Inicio</th>
            <th>Término</th>
            <th>Turno</th>
            <th>Alumnos</th>
          </tr>
          </thead>
          <tbody>
          {
            isLoading ?
              <tr>
                <th className='text-center' colSpan={14}>Cargando información...</th>
              </tr>
              : reporteCiclos.length > 0 ?
              reporteCiclos.map( ( campo ) => (
                <tr key={++indexRow}>
                  <td> {campo.unidad.delegacion.nombre} </td>
                  <td> {campo.unidad.nombre} </td>
                  <td> {campo.convenio.carrera.displayName} </td>
                  <td> {campo.asignatura} </td>
                  <td> {campo.convenio.institucion.nombre} </td>
                  <td> {campo.fechaInicialFormatted} </td>
                  <td> {campo.fechaFinalFormatted} </td>
                  <td> {campo.horario} </td>
                  <td> {campo.lugaresAutorizados} </td>
                </tr>
              ))
              :
              <tr>
                <td colSpan={10} className="text-center"> No hay registros disponibles </td>
              </tr>
          }
          </tbody>
        </table>
        { isLoading ? null
          : reporteCiclos.length > 0 ?
            <div className="col-md-12">
              <div className="col-md-3">
                {offset} - {offset + (reporteCiclos.length - 1)} de {totalItems}
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
            : null
        }
      </div>
    </div>
  );
};

const Filtros = (props) => {
  const {useState, useEffect} = React
  const [delegaciones, setDelegaciones] = useState([])
  const [unidades, setUnidades] = useState([])
  const [carreras, setCarreras] = useState([])
  const [ciclos, setCiclos] = useState([])

  useEffect(() => {
    getDelegaciones()
      .then((res) => setDelegaciones(res.data))
    getCarreras()
      .then((res) => setCarreras(res.data))
    getCiclosAcademicos()
      .then((res) => setCiclos(res.data))
  }, []);

  function handleDelegacion(delegacion) {
    props.setDelegacionSel(delegacion);
    getUnidades(delegacion)
      .then((res)=> setUnidades(res.data));
  }

  return (
    <React.Fragment>
      <div className="row">
        <ContenedorFiltro
          EtiquetaFiltro={"Delegación"}
          setValSel={handleDelegacion}
          valores={delegaciones}
          name="delegacion"
          tipo="Select"
        />
        <ContenedorFiltro
          EtiquetaFiltro={"Unidad"}
          setValSel={props.setUnidadSel}
          valores={unidades}
          name="unidad"
          tipo="Select"
          defaultOptionText="Elige primero una Delegación"
        />
        <ContenedorFiltro
          EtiquetaFiltro={"Carrera"}
          setValSel={props.setCarreraSel}
          valores={carreras}
          name="carrera"
          tipo="Select"
        />
        <ContenedorFiltro
          EtiquetaFiltro={"Ciclo Académico"}
          setValSel={props.setCicloSel}
          valores={ciclos}
          name="ciclo"
          tipo="Select"
        />
        <div className="col-md-2">Período de Consulta de ciclos clínicos</div>
        <ContenedorFiltro
          EtiquetaFiltro={"Desde: "}
          setValSel={props.setDesde}
          valores={[]}
          name="desde"
          tipo="date"
        />
        <ContenedorFiltro
          EtiquetaFiltro={"Hasta: "}
          setValSel={props.setHasta}
          valores={[]}
          name="desde"
          tipo="date"
        />
      </div>
      <Buscador
        handleSearch={ props.handleSearch }
        handleExport={ props.exportar }
        setSearch={props.setSearch}
      />
    </React.Fragment>
  );
}

export default ReporteUnidad