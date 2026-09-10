import * as React from "react";

const OpcionesPageSize = ({ setPageSize, handleSearch }) => {

  function handlerPageSize(e) {
    setPageSize(parseInt(e.value));
    handleSearch(1, e.value);
  }

  return (
    <label> Mostrar
      <select
        onChange={({target}) => handlerPageSize(target)}>
        <option value='10'>10</option>
        <option value='30'>30</option>
        <option value='50'>50</option>
      </select>
      registros
    </label>
  );
}

export default OpcionesPageSize