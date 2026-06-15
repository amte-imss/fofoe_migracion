import * as React from 'react'
import {SOLICITUD, TIPO_PAGO} from "../../../constants";
import {getSchemeAndHttpHost} from "../../../utils";

const FILTERS_FOR_ORDERING = [];
FILTERS_FOR_ORDERING.NO_SOLICITUD_MENOR_A_MAYOR = 'order_by_no_solicitud_menor_a_mayor';
FILTERS_FOR_ORDERING.NO_SOLICITUD_MAYOR_A_MENOR = 'order_by_no_solicitud_mayor_a_menor';
FILTERS_FOR_ORDERING.FECHA_DE_SOLICITUD_MAS_RECIENTE = 'order_by_fecha_de_solicitud_mas_reciente';
FILTERS_FOR_ORDERING.FECHA_DE_SOLICITUD_MAS_ANTIGUA = 'order_by_fecha_de_solicitud_mas_antigua';

const FiltroTablaSolicitudes = ({
                                  setTipoPago, tipoPago,
                                  setEstatus, estatus,
                                  setOrderBy, orderBy,
                                  setSearch, search,
                                  handleSearch,
                                  cleanFilters
                                }) => {

  return (
    <div>
      <div className='row'>
        <div className="col-md-3">
          <div className="form-group">
            <label htmlFor="solicitud_estado">Estado de la solicitud</label>
            <select
              id="solicitud_estado"
              className='form-control'
              onChange={({ target }) => setEstatus(target.value)}
              value={estatus}
            >
              <option value=''>Ver todos</option>
              {
                Object.values(SOLICITUD).map(item => {
                  if(item === SOLICITUD.CREADA) return;
                  return <option value={item} key={item}>{item}</option>;
                })
              }
            </select>
          </div>
        </div>
        <div className="col-md-3">
          <div className="form-group">
            <label htmlFor="solicitud_tipoPago">Ordenar por</label>
            <select
              id="solicitud_tipoPago"
              className='form-control'
              onChange={({ target }) => setOrderBy(target.value)}
              value={orderBy}
            >
              <option value=''>Ver todos</option>
              <option value={FILTERS_FOR_ORDERING.NO_SOLICITUD_MAYOR_A_MENOR}>No. de solicitud. de mayor a menor</option>
              <option value={FILTERS_FOR_ORDERING.NO_SOLICITUD_MENOR_A_MAYOR}>No. de solicitud. de menor a mayor</option>
              <option value={FILTERS_FOR_ORDERING.FECHA_DE_SOLICITUD_MAS_RECIENTE}>Fecha de solicitud. más reciente</option>
              <option value={FILTERS_FOR_ORDERING.FECHA_DE_SOLICITUD_MAS_ANTIGUA}>Fecha de solicitud. más antigua</option>
            </select>
          </div>
        </div>
        <div className='col-md-4 mt-20 text-right'>
          <div className='navbar-form navbar-right'>
            <div className="form-group">
              <input
                type="text"
                placeholder='Buscar por No. de solicitud o fecha...'
                className='input-sm form-control'
                onChange={({ target }) => setSearch(target.value)}
                style={{ width: 250 }}
                value={search}
              />
            </div>
            <button
              type="button"
              className="btn btn-default"
              onClick={handleSearch}
            >
              Buscar
            </button>
          </div>
        </div>
        <div className="col-md-2 mt-20">
          <div className="form-group">
            <button
              className="btn btn-default btn-block form-control"
              onClick={cleanFilters}
            >
              Limpiar filtros
            </button>
          </div>
        </div>
      </div>
      <div className="row mb-10" >
        <div className="col-md-2" >
          <a href={`${getSchemeAndHttpHost()}/ie/solicitudes/nueva`}
             id="btn_solicitud"
             className='btn btn-lg btn-success' >
            Registrar Nueva Solicitud
          </a>
        </div>
        <div className="col-md-7" />
      </div>
    </div>
  )
}

export default FiltroTablaSolicitudes