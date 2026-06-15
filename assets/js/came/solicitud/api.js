import {getSchemeAndHttpHost} from "../../utils";

export function uploadOficioMontos(solicitud, formData) {
    return fetch(`${getSchemeAndHttpHost()}/came/solicitud/${solicitud}/upload-oficio-montos`, {
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        method: 'post',
        body: formData
    }).then(response => response.json())
}
