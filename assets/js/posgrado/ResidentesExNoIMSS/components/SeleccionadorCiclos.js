import React from "react";
import {getSchemeAndHttpHost} from "../../../utils";

const SeleccionadorCiclos = ({ciclos, meta, setMeta, handleSearchEvent, tipo = 'EXTRANJERO_NO_IMSS'}) => {

  return (
    <div className="row">
      <div className="col-md-2">
        <div className={``}>
          <label htmlFor="year">Ciclo: </label>
          <select id="year" className="form-control"
                  defaultValue={meta.ciclo}
                  onChange={e => {setMeta(Object.assign(meta, {ciclo: e.target.value}));  handleSearchEvent(); }}>
            {
              ciclos.map(item => {
                return (<option key={`${item.ciclo}`} value={`${item.ciclo}`}>{item.ciclo}</option>)
              })}
          </select>
          <span className="help-block"> </span>
        </div>
      </div>
      <div className={"col-md-6 mt-20"}>
        <div className={"form-group"}>
          <input name="query"  className={"form-control"}
                 type="text" placeholder={'Nombre / Folio / Especialidad / Nacionalidad'}
                 width={""}
                 defaultValue={meta.query}
                 onChange={e => {
                   setMeta(Object.assign(meta, {query: e.target.value}));}}
          />
        </div>
        <span className="help-block"> </span>
      </div>
      <div className={"col-md-2 mt-20"}>
        <div className={"form-group"}>
          <button
            className={"form-control btn btn-success"}
            onClick={handleSearchEvent}
          >
            Buscar
          </button>
        </div>
      </div>

      {tipo === 'EXTRANJERO_NO_IMSS' ?
      <div className={"col-md-2 mt-20"}>
        <div className={"form-group"}>
          <a
            href={`${getSchemeAndHttpHost()}/posgrado/residentes/no_imss/nuevo`}
            className={"form-control btn btn-info"}
          >
            Nuevo Registro
          </a>
        </div>
      </div>
      : null }
      <div className={"col-md-2 mt-20"}>
        <div className={"form-group"}>
          <a
            href={`${getSchemeAndHttpHost()}/test`}
            target={'_blank'}
            className={"form-control btn btn-primary"}
          >
            Exportar
          </a>
        </div>
      </div>
    </div>
  )
}

export default SeleccionadorCiclos