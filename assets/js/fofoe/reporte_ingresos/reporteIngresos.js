import {getSchemeAndHttpHost} from "../../utils";

const getReporteIngresos = (anio) => {
  return fetch(`${getSchemeAndHttpHost()}/fofoe/reporte_ingresos?anio=${anio}`)
    .then(function(response) {
      return response.json();
    })
}

export {
  getReporteIngresos
}