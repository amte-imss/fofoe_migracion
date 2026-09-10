import {clickAction} from "../utils"
import {getSchemeAndHttpHost} from "../../utils";

const getCamposClinicos = ( tipoCASel, delegacionSel, carreraSel, estadoSolSel,
                            fechaIniSel, fechaFinSel,
                            search, page, limit) => {
  return fetch(`${getSchemeAndHttpHost()}/pregrado/reporte/?page=${page}&limit=${limit}&search=${search}`
    + `&cicloAcademico=${tipoCASel}&estatus=${estadoSolSel}`
    + `&fechaIni=${fechaIniSel}&fechaFin=${fechaFinSel}`
    + `&delegacion=${delegacionSel}&carrera=${carreraSel}`)
    .then(function(response) {
      return response.json();
    })
}

const getCamposClinicosCSV = ( tipoCASel, delegacionSel, carreraSel, estadoSolSel,
                               fechaIniSel, fechaFinSel, search) => {
  return clickAction(`${getSchemeAndHttpHost()}/pregrado/reporte/?search=${search}`
    + `&cicloAcademico=${tipoCASel}&estatus=${estadoSolSel}`
    + `&fechaIni=${fechaIniSel}&fechaFin=${fechaFinSel}`
    + `&delegacion=${delegacionSel}&carrera=${carreraSel}`
    + `&export=1`);
}

export {
  getCamposClinicos, getCamposClinicosCSV
}
