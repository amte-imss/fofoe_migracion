import {getSchemeAndHttpHost} from "../../utils";

const conveniosGet = () => {
  return  fetch(`${getSchemeAndHttpHost()}/ie/api/convenio`)
    .then(response => {
        return response.json()},
      error => {
        console.error(error)
      })
}

export {conveniosGet}