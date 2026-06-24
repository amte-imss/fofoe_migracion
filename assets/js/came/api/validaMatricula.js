import {getSchemeAndHttpHost} from "../../utils";

const validaMatriculaGet = (matricula, delegacion) => {
  return fetch(`${getSchemeAndHttpHost()}/came/api/validar_matricula/${matricula}/${delegacion}`)
    .then(function(response) {
      return response.json();
    })
}

export {
  validaMatriculaGet
}
