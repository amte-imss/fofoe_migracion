import * as React from 'react'
import {getCamposClinicos, getCamposClinicosCSV} from "../reporte/campos";
import {getCarreras, getCiclosAcademicos, getDelegaciones, getEstatusCampoClinico} from "../api/catalogos";
import ContenedorFiltro from "./ContenedorFiltro";
import Buscador from "./Buscador";
import OpcionesPageSize from "./OpcionesPageSize";
import ReactPaginate from "react-paginate";

const ReporteDetalle = () => {
  const {useState, useEffect} = React

  const [search, setSearch] = useState('')
  const [isLoading, toggleLoading] = useState(false)
  const [totalItems, setTotalItems] = useState(0)
  const [totalPages, setTotalPages] = useState(0)
  const [currentPage, setCurrentPage] = useState(1)
  const [pageSize, setPageSize] = useState(10)

  const [camposClinicos, setCamposClinicos] = useState([])
  const [carreraSel, setCarreraSel] = useState(null)
  const [cicloAcademicoSel, setCASel] = useState(null)
  const [delegacionSel, setDelegacionSel] = useState(null)
  const [estadoSolSel, setEstadoSolSel] = useState(null)
  const [fechaIniSel, setFechaIniSel] = useState(null)
  const [fechaFinSel, setFechaFinSel] = useState(null)

  useEffect(() => {
    getCampos();
  }, []);

  function handleSearch(pag=1, limit=pageSize) {
    getCampos(pag, limit)
  }

  function handleExport() {
    getCamposClinicosCSV(
      cicloAcademicoSel, delegacionSel, carreraSel,
      estadoSolSel, fechaIniSel, fechaFinSel, search);
  }

  function getCampos(pag=1, limit=pageSize) {
    pag = Number.isInteger(pag) ? pag : 1;
    toggleLoading(true);
    getCamposClinicos(
      cicloAcademicoSel, delegacionSel, carreraSel,
      estadoSolSel, fechaIniSel, fechaFinSel,
      search, pag, limit
    ).then((res) => {
      setCamposClinicos(res.camposClinicos)
      setTotalItems( res.totalItems )
      setTotalPages( res.numPags)
      setCurrentPage(pag)
    })
      .finally(() => {
        toggleLoading(false)
      })
  }

  return (
    <div className="panel panel-default">
      <div className="panel-heading">
        Reporte detallado de campos clínicos - Ciclos Clínicos e Internado Médico
      </div>
      <div className="panel-body">
        <Filtros
          setCarreraSel={setCarreraSel}
          setCASel={setCASel}
          setDelegacionSel={setDelegacionSel}
          setEstadoSolSel={setEstadoSolSel}
          setFechaIniSel={setFechaIniSel}
          setFechaFinSel={setFechaFinSel}

          setSearch={setSearch}
          handleSearch={handleSearch}
          handleExport={handleExport}
          setPageSize={setPageSize}
          pageSize={pageSize}
        />
        <TablaCampos
          isLoading={isLoading}
          camposClinicos={camposClinicos}
          pageSize={pageSize}
          setPageSize={setPageSize}
          totalPages={totalPages}
          totalItems={totalItems}
          currentPage={currentPage}
          getCampos={getCampos}
          handleSearch={handleSearch}
        />
      </div>
    </div>
  );
}

const Filtros = (
  props
) => {

  const {useEffect, useState} = React
  const [carreras, setCarreras] = useState([])
  const [tiposCA, setTipos] = useState([])
  const [delegaciones, setDelegaciones] = useState([])
  const [estadosSol, setEstadosSol] = useState([])

  useEffect(() => { // catalogos
    getCarreras()
      .then((res) => setCarreras(res.data))
    getDelegaciones()
      .then((res) => setDelegaciones(res.data))
    getCiclosAcademicos()
      .then((res) => setTipos(res.data))
    getEstatusCampoClinico()
      .then((res) => setEstadosSol(res))
  }, []);

  return (
    <React.Fragment>
      <div className="row">
        {/*
        <ContenedorFiltro
          EtiquetaFiltro="Ciclo Académico"
          name="CicloAcademico"
          valores={tiposCA}
          setValSel={props.setCASel}
          tipo="Select"
        />
        <ContenedorFiltro
          EtiquetaFiltro="Delegación"
          name="Delegacion"
          valores={delegaciones}
          setValSel={props.setDelegacionSel}
          tipo="Select"
        />
        */}
        <ContenedorFiltro
          EtiquetaFiltro="Carrera"
          name="Carrera"
          valores={carreras}
          setValSel={props.setCarreraSel}
          tipo="Select"
        />
        <ContenedorFiltro
          EtiquetaFiltro="Estado de la Solicitud"
          name="EstadoSol"
          valores={estadosSol}
          setValSel={props.setEstadoSolSel}
          tipo="Select"
        />
        <ContenedorFiltro
          EtiquetaFiltro="Fecha incio a partir de:"
          name="FechaInicio"
          valores={[]}
          setValSel={props.setFechaIniSel}
          tipo="date"
        />
        <ContenedorFiltro
          EtiquetaFiltro="Fecha de fin antes de:"
          name="FechaInicio"
          valores={[]}
          setValSel={props.setFechaFinSel}
          tipo="date"
        />
      </div>
      <Buscador
        setSearch={props.setSearch}
        handleSearch={props.handleSearch}
        handleExport={props.handleExport}
        setPageSize={props.setPageSize}
        pageSize={props.pageSize}
      />
    </React.Fragment>
  );
}

const TablaCampos = (props) => {

  let offset = props.totalItems > 0 ?
    (props.pageSize*(props.currentPage-1)) + 1
    : 0;

  function handlePageClick(e) {
    props.getCampos( e.selected + 1);
  }

  return (
    <div className="col-md-12">
      <div className="panel panel-default">
        <div className="panel-body">
          <OpcionesPageSize
            setPageSize={props.setPageSize}
            handleSearch={props.handleSearch}
          />
          <div style={{overflowX: 'auto', width: '100%'}}>
          <table className="table" style={{minWidth: '1800px', tableLayout: 'fixed'}}>
            <thead>
            <tr>
              <td style={{minWidth: '100px'}}>Número de Solicitud</td>
              <td style={{minWidth: '110px'}}>Fecha de Solicitud</td>
              <td style={{minWidth: '150px'}}>OOAD</td>
              <td style={{minWidth: '150px'}}>Unidad</td>
              <td style={{minWidth: '200px'}}>Institución Educativa</td>
              <td style={{minWidth: '100px'}}>RFC</td>
              <td style={{minWidth: '120px'}}>Ciclo Académico</td>
              <td style={{minWidth: '150px'}}>Carrera</td>
              <td style={{minWidth: '150px'}}>Asignatura</td>
              <td style={{minWidth: '80px'}}>Núm. lugares solicitados</td>
              <td style={{minWidth: '80px'}}>Núm. lugares autorizados</td>
              <td style={{minWidth: '100px'}}>Fecha Inicio</td>
              <td style={{minWidth: '100px'}}>Fecha de Conclusión</td>
              <td style={{minWidth: '120px'}}>Número de Referencia</td>
              <td style={{minWidth: '100px'}}>Importe del Pago</td>
              <td style={{minWidth: '100px'}}>Fecha del Pago</td>
              <td style={{minWidth: '100px'}}>Número de Factura</td>
              <td style={{minWidth: '120px'}}>Estado de la solicitud</td>
            </tr>
            </thead>
            <tbody>
            {
              props.isLoading ?
                <tr>
                  <th className='text-center' colSpan={18}>Cargando información...</th>
                </tr> :
                props.camposClinicos.length > 0 ?
                  props.camposClinicos.map((campoClinico, index) => (
                    <tr key={index}>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.solicitud.noSolicitud}</td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.solicitud.fecha}</td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.unidad && campoClinico.unidad.delegacion ? campoClinico.unidad.delegacion.nombre : ""}</td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.unidad ? campoClinico.unidad.nombre : ""}</td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.convenio.institucion.nombre}</td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.convenio.institucion.rfc || ''}</td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.convenio.cicloAcademico ? campoClinico.convenio.cicloAcademico.nombre : ""}</td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.convenio.carrera
                        ?  campoClinico.convenio.carrera.displayName
                        : ''}
                      </td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.asignatura} </td>
                      <td style={{wordWrap: 'break-word', textAlign: 'center'}}>{campoClinico.lugaresSolicitados}</td>
                      <td style={{wordWrap: 'break-word', textAlign: 'center'}}>{campoClinico.lugaresAutorizados}</td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.displayFechaInicial}</td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.displayFechaFinal}</td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.referenciaBancaria || (campoClinico.solicitud.referenciaBancaria || '')}</td>
                      <td style={{wordWrap: 'break-word', textAlign: 'right'}}>{campoClinico.lastPago ? '$' + Number(campoClinico.lastPago.monto).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : ''}</td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.lastPago ? campoClinico.lastPago.fechaPagoRegistradaFormatted : ''}</td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.lastPago && campoClinico.lastPago.factura ? campoClinico.lastPago.factura.folio : ''}</td>
                      <td style={{wordWrap: 'break-word'}}>{campoClinico.estatus ? (campoClinico.estatus.nombre !== 'Credenciales generadas' ? campoClinico.estatus.nombre : 'Pago' ) : '?'}</td>
                    </tr>
                  ))
                  : <tr>
                    <td className='text-center' colSpan={18}>No hay registros disponibles</td>
                  </tr>
            }
            </tbody>
          </table>
          </div>
          { !props.isLoading && props.camposClinicos.length > 0 ?
            <div className="col-md-12">
              <div className="col-md-3">
                {offset} - {offset +
              (props.camposClinicos.length - 1)} de {props.totalItems}
              </div>
              <div className="col-md-9 text-center">
                <ReactPaginate
                  pageCount={props.totalPages}
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
                  forcePage={props.currentPage - 1}
                />
              </div>
            </div>
            : ''
          }
        </div>
      </div>
    </div>
  );
};

export default ReporteDetalle