import {getSchemeAndHttpHost} from "../../utils";

const getEstatusCampoClinico = () => {
  return fetch(`${getSchemeAndHttpHost()}/estatus-campos-clinicos`)
    .then(function(response) {
      return response.json();
    })
}

export {
  getEstatusCampoClinico
}
