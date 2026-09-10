import {getSchemeAndHttpHost} from "../../utils";

const getCarreras = () => {
  return fetch(`${getSchemeAndHttpHost()}/api/pregrado/carrera`)
    .then(function (response) {
      return response.json();
    }).then(function (json) {
      json.data.forEach(function (obj) {
        obj['nombre'] = obj.nivelAcademico.nombre.concat(" - ", obj.nombre);
      });
      return json;
    });
}

const getCiclosAcademicos = () => {
  return fetch(`${getSchemeAndHttpHost()}/api/pregrado/ciclo_academico`)
    .then(function (response) {
      return response.json()
    })
}

const getDelegaciones = () => {
  return fetch(`${getSchemeAndHttpHost()}/api/pregrado/delegacion`)
    .then(function (response) {
      return response.json()
    })
}

const getEstatusCampoClinico = () => {
  return fetch(`${getSchemeAndHttpHost()}/estatus-campos-clinicos`)
    .then(function (response) {
      return response.json();
    })
}

const getUnidades = (delegacion) => {
  return fetch(`${getSchemeAndHttpHost()}/api/pregrado/unidad/${delegacion}`)
    .then(function(response) {
      return response.json();
    })
}

export {
  getCarreras, getCiclosAcademicos, getDelegaciones, getUnidades, getEstatusCampoClinico
}
