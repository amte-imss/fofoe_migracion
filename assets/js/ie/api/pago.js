import {getSchemeAndHttpHost} from "../../utils";

const getGestionPagoAsync = (id) => {
  return fetch(`${getSchemeAndHttpHost()}/ie/pagos/${id}/gestion-de-pago`)
    .then(function(response) {
      return response.json();
    })
}

export {
  getGestionPagoAsync
}
