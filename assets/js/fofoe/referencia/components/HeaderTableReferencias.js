import * as React from "react";
import {CATEGORIA_PAGO_HEADERS, FOFOE_ESTADOS_PAGOS, FOFOE_TIPO_PAGOS} from "../../constants";

const HeaderCC = ({setQuery, query, meta, setMeta, handleSearchEvent }) => {
  return(
    <thead>
    <tr>
      <th>OOAD <br /> / UMAE</th>
      <th>Institución Educativa</th>
      <th>No. de Solicitud</th>
      <th>No. de Referencia</th>
      <th>Monto</th>
      <th>Factura</th>
      <th>Fecha Registro de Pago</th>
      <th>Estado</th>
      <th></th>
    </tr>
    <tr style={{background: 'lightgray'}}>
      <th>
        <input type="text" placeholder={'OOAD'}
               onChange={e => {setQuery(Object.assign(query,{delegacion: e.target.value})); handleSearchEvent()}}/>
      </th>
      <th>
        <input type="text" placeholder="Institución"
               onChange={e => {setQuery(Object.assign(query,{ institucion : e.target.value})); handleSearchEvent()}}/></th>
      <th><input type="text" placeholder="No. de Solicitud"
                 onChange={e => {setQuery(Object.assign(query,{ no_solicitud : e.target.value})); handleSearchEvent() }}/></th>
      <th><input type="text" placeholder={'No. de Referencia'}
                 onChange={e => {setQuery(Object.assign(query,{referencia: e.target.value})); handleSearchEvent()}}/></th>
      <th><input type="number" placeholder={'Monto'}
                 onChange={e => {setQuery(Object.assign(query,{monto: e.target.value})); handleSearchEvent()}}/></th>
      <th><input type="text" placeholder={'Folio Factura'}
                 onChange={e => {setQuery(Object.assign(query,{factura: e.target.value})); handleSearchEvent()}}/></th>
      <th> </th>
      <th>
        {
          !meta.estado &&
          <select className="form-control"
                  defaultValue={meta.estado}
                  onChange={e => { setMeta(Object.assign(meta, {page: 1})); setQuery(Object.assign(query, {estado: e.target.value}));  handleSearchEvent(); }}>
            <option value="">Todos</option>
            <option value="a">Pendiente Validación</option>
            <option value="b">Solicitud Pagada</option>
            <option value="d">Pago no Válido</option>
            <option value="c">Factura Pendiente</option>
          </select>
        }
      </th>
      <th> </th>
    </tr>
    </thead>
  )
}

const HeaderPosgrado = ({setQuery, query, meta, setMeta, handleSearchEvent }) => {
  return(
    <thead>
    <tr>
      <th>OOAD <br /> / UMAE</th>
      <th>Nombre</th>
      <th>No. de Referencia</th>
      <th>Monto</th>
      <th>Factura</th>
      <th>Fecha Registro de Pago</th>
      <th>Estado</th>
      <th></th>
    </tr>
    <tr style={{background: 'lightgray'}}>
      <th>
        <input type="text" placeholder={'OOAD'}
               onChange={e => {setQuery(Object.assign(query,{delegacion: e.target.value})); handleSearchEvent()}}/>
      </th>
      <th>
        <input type="text" placeholder="Nombre"
               onChange={e => {setQuery(Object.assign(query,{ residente : e.target.value})); handleSearchEvent()}}/></th>
      <th><input type="text" placeholder={'No. de Referencia'}
                 onChange={e => {setQuery(Object.assign(query,{referencia: e.target.value})); handleSearchEvent()}}/></th>
      <th>{
        /*
        <input type="number" placeholder={'Monto'}
               onChange={e => {setQuery(Object.assign(query,{monto: e.target.value})); handleSearchEvent()}}/>
               */
      }</th>
      <th>
        {/*
          <input type="text" placeholder={'Folio Factura'}
                 onChange={e => {setQuery(Object.assign(query,{factura: e.target.value})); handleSearchEvent()}}/>
           */
        }</th>
      <th> </th>
      <th>
        {
          !meta.estado &&
          <select className="form-control"
                  defaultValue={meta.estado}
                  onChange={e => { setMeta(Object.assign(meta, {page: 1})); setQuery(Object.assign(query, {estado: e.target.value}));  handleSearchEvent(); }}>
            <option value="">Todos</option>
            <option value="pendiente_val">Pendiente Validación</option>
            <option value="validado_facturado">Residencia Pagada</option>
            <option value="no_valido">Pago no Válido</option>
            <option value="pendiente_factura">Factura Pendiente</option>
          </select>
        }
      </th>
      <th> </th>
    </tr>
    </thead>
  )
}

const HeaderTableReferencias = ({setQuery, query, meta, setMeta, handleSearchEvent, categoriaPago }) => {

  return(
      'CC' === categoriaPago ?
       <HeaderCC
         setQuery={setQuery}
         query={query}
         meta={meta}
         setMeta={setMeta}
         handleSearchEvent={handleSearchEvent}
         />
      : (
        'POSGRADO' === categoriaPago ?
          <HeaderPosgrado
            setQuery={setQuery}
            query={query}
            meta={meta}
            setMeta={setMeta}
            handleSearchEvent={handleSearchEvent}
          />
          : null
      )
  )
}

export default HeaderTableReferencias