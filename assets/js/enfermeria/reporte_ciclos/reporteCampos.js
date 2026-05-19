import {clickAction} from "../../pregrado/utils";
import {getSchemeAndHttpHost} from "../../utils";

const getReporteCampos = (
  desde, hasta, delegacion, unidad, carrera, ciclo,
  search, page, limit
) => {
  let urlQuery = `${getSchemeAndHttpHost()}/enfermeria/reporte_ciclos?search=${search}`
    + `&fechaIni=${desde}&fechaFin=${hasta}`
    + `&delegacion=${delegacion}&unidad=${unidad}`
    + `&carrera=${carrera}&cicloAcademico=${ciclo}`
    + `&page=${page}&limit=${limit}`;

  return fetch(urlQuery)
    .then(function(response) {
      return response.json();
    })
}

const getReporteCamposCSV = (
  desde, hasta, delegacion, unidad,
  carrera, ciclo, search
) => {

  let urlQuery = `${getSchemeAndHttpHost()}/enfermeria/reporte_ciclos?search=${search}`
    + `&fechaIni=${desde}&fechaFin=${hasta}`
    + `&delegacion=${delegacion}&unidad=${unidad}`
    + `&carrera=${carrera}&cicloAcademico=${ciclo}`
    + `&export=1`;

  return clickAction(urlQuery);
}

export {
  getReporteCampos, getReporteCamposCSV
}