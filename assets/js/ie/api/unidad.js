import {getSchemeAndHttpHost} from "../../utils";

const umaesGet = () => {
  return fetch(`${getSchemeAndHttpHost()}/ie/api/unidad/umae`)
    .then(response => {
        return response.json()},
      error => {
        console.error(error)
      })
}

const umaesNoSelcsGet = () => {
  return fetch(`${getSchemeAndHttpHost()}/ie/api/unidad/umae/no_selecs`)
    .then(response => {
        return response.json()},
      error => {
        console.error(error)
      })
    .then(json => {
      return json.data
    })
}

const unidadesByDelegacionGet = (idDel) => {
  return fetch(`${getSchemeAndHttpHost()}/ie/api/unidad/delegacion/${idDel}`)
    .then(response => {
        return response.json()},
      error => {
        console.error(error)
      })
}

export {umaesGet, unidadesByDelegacionGet, umaesNoSelcsGet}