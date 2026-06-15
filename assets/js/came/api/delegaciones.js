import {getSchemeAndHttpHost} from "../../utils";

const delegacionesGet = () => {
  return fetch(`${getSchemeAndHttpHost()}/came/api/delegacion`)
    .then(function(response) {
      return response.json();
    })
}

export {
  delegacionesGet
}
