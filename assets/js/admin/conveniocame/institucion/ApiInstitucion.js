import AbstracApi from "../../../api/AbstracApi";
import {getSchemeAndHttpHost} from "../../../utils";

class ApiInstitucion extends AbstracApi {
    constructor() {
        super();
    }

    paginateInstituciones(filters) {
        let url = `${getSchemeAndHttpHost()}/admin/convenioscame/institucion`;
        const searchParams = new URLSearchParams(filters);
        return fetch(`${url}?${searchParams.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => this.toJson(response))
    }

    storeUsuario(institucionId, formData) {
        let url = `${getSchemeAndHttpHost()}/admin/convenioscame/institucion/${institucionId}/usuario`;
        return fetch(url, {
            method: 'POST',
            body: formData
        }).then(response => this.toJson(response))
    }

    storeCampus(institucionId, formData) {
        let url = `${getSchemeAndHttpHost()}/admin/convenioscame/institucion/${institucionId}/campus`;
        return fetch(url, {
            method: 'POST',
            body: formData
        }).then(response => this.toJson(response))
    }

    getCampus(institucionId) {
        let url = `${getSchemeAndHttpHost()}/admin/convenioscame/institucion/${institucionId}/campus`;
        return fetch(url, {
            method: 'GET'
        }).then(response => this.toJson(response))
    }
}

const apiInstitucion = new ApiInstitucion();
export default apiInstitucion;