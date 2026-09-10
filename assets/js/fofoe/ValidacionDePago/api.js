import {getSchemeAndHttpHost} from "../../utils";

export function validateOficioMontos(solicitud, status, motive) {
    return fetch(`${getSchemeAndHttpHost()}/fofoe/solicitud/${solicitud}/validate-oficio-montos`, {
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        method: 'post',
        body: JSON.stringify({validate_oficio_montos: status, motive_oficio_montos: status ? null: motive })
    }).then(response => response.json())
}

export function validateFormatoFofoe(campoClinico, status, motive) {
    return fetch(`${getSchemeAndHttpHost()}/fofoe/campo-clinico/${campoClinico}/validate-formato-fofoe`, {
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        method: 'post',
        body: JSON.stringify({validate_formato_fofoe: status, motive_formato_fofoe: status ? null : motive })
    }).then(response => response.json())
}