import * as React from "react";
import {CATEGORIA_PAGO_ORDEN} from "../../constants";

const SeleccionadorOrdenReferencias = ({query, setQuery, handleSearchEvent, categoriaPago}) => {
  return (
    <div className="col-md-4">
      <div className={``}>
        <label htmlFor="orderby">Ordenar por: </label>
        <select id="orderby" className="form-control"
                onChange={e => {setQuery(Object.assign(query, {orderby: e.target.value}));  handleSearchEvent(); }}>
          {
            Object.keys(CATEGORIA_PAGO_ORDEN[categoriaPago]).map((keyCat, index) => {
              return (
                <option key={index} value={keyCat}>{CATEGORIA_PAGO_ORDEN[categoriaPago][keyCat]}</option>
              )
            })
          }
        </select>
        <span className="help-block"> </span>
      </div>
    </div>
  )
}

export default SeleccionadorOrdenReferencias
