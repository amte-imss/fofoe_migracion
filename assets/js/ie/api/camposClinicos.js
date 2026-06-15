import {getSchemeAndHttpHost} from "../../utils";

const solicitudesGet = (solicitudId, search) => {
  return fetch(`${getSchemeAndHttpHost()}/ie/solicitudes/${solicitudId}/detalle-de-solicitud?search=${search}`)
    .then(function (response) {
      return response.json();
    })
}

const uploadComprobantePago = (id, file) => {
  const form = new FormData();
  form.append('comprobantePago[campoClinico]', id);
  form.append('comprobantePago[file]', file[0]);

  return fetch(`${getSchemeAndHttpHost()}/ie/cargar-comprobante-de-pago`, {
    method: 'POST',
    body: form
  }).then((res) => res.json())
}

export {
  solicitudesGet,
  uploadComprobantePago
}
