import {getSchemeAndHttpHost} from "../../utils";

const solicitudesGet = (tipoPago, estatus, currentPage, perPage, orderBy, search) => {
  return fetch(`${getSchemeAndHttpHost()}/ie/inicio?offset=${currentPage}&perPage=${perPage}&orderBy=${orderBy}&search=${search}&tipoPago=${tipoPago}&estatus=${estatus}`)
    .then(function(response) {
      return response.json();
    })
}

const solicitudCreate = (idDel) => {
  return fetch(`${getSchemeAndHttpHost()}/ie/api/solicitud`, {
      method: 'post'
    }).then((response) => {
      return response.json();
    }).then((json) => {
      json.data;
    } )
}

export {
  solicitudesGet
}
