import {getSchemeAndHttpHost} from "../../utils";

const delegacionesGet = () => {
  return  fetch(`${getSchemeAndHttpHost()}/ie/api/delegacion`)
    .then(response => {
        return response.json()},
      error => {
        console.error(error)
      })
}

const delegacionesNoSelecsGet = () => {
  return  fetch(`${getSchemeAndHttpHost()}/ie/api/delegacion/no_selecs`)
    .then(response => {
        return response.json()},
      error => {
        console.error(error)
      })
    .then(json => {
      return json.data
    })
}

const delegacionesConveniosGet = () => {
  return  fetch(`${getSchemeAndHttpHost()}/ie/api/delegacion/convenios`)
    .then(response => {
        return response.json()},
      error => {
        console.error(error)
      })
    .then(json => {
      return json.data
    })
}

export {delegacionesGet, delegacionesNoSelecsGet}